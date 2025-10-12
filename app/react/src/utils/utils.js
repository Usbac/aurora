import React, { useCallback, useEffect, useRef, useState } from 'react';
import { IconFolderFill, IconHome, IconUploadFile, IconX } from './icons';
import { createPortal } from 'react-dom';
import axios from 'axios';

export const makeRequest = async ({ method = 'GET', url, data = null, options = {} }) => {
    const form_data = new FormData();

    if (data) {
        Object.keys(data).forEach(key => {
            let val = data[key];

            if (typeof val === 'boolean') {
                val = val ? '1' : '0';
            }

            form_data.append(key, val);
        });
    }

    let headers = {
        Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
    };

    if ([ 'DELETE', 'PUT' ].includes(method)) {
        headers['Content-Type'] = 'application/json';
    }

    try {
        const res = await axios({
            method,
            url,
            headers: headers,
            data: form_data,
            ...options,
        });

        return res;
    } catch (err) {
        console.error(err);
        throw err;
    }
};

export const useRequest = (params) => {
    const [ data, setData ] = useState(null);
    const [ is_loading, setIsLoading ] = useState(true);
    const [ is_error, setIsError ] = useState(false);

    const fetch = useCallback(async () => {
        setIsLoading(true);
        setIsError(false);

        try {
            const res = await makeRequest(params);
            setData(res);
        } catch (err) {
            setIsError(true);
        } finally {
            setIsLoading(false);
        }
    }, [ JSON.stringify(params) ]);

    return {
        data: data,
        is_loading: is_loading,
        is_error: is_error,
        fetch: fetch,
    };
};

export const useElement = (url) => {
    const { data, is_loading, is_error, fetch } = useRequest({
        method: 'GET',
        url: url,
    });

    useEffect(() => {
        fetch();
    }, []);

    if (is_loading) {
        return undefined;
    }

    return data?.data && !is_error ? data.data : null;
};

export const MenuButton = () => <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list pointer" viewBox="0 0 16 16" onClick={() => document.body.toggleAttribute('data-nav-open')}>
    <path fillRule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
</svg>;

export const LoadingPage = () => <div className="content">
    <div className="loading-page">
        <div className="spinner"/>
    </div>
</div>;

export const Input = (props) => {
    const char_count = props.value?.length || 0;

    return <>
        <input {...props}/>
        {props.charCount && <span class="char-counter">{char_count} character{char_count !== 1 ? 's' : ''}</span>}
    </>;
};

export const Textarea = (props) => {
    const char_count = props.value?.length || 0;

    return <>
        <textarea {...props}></textarea>
        {props.charCount && <span class="char-counter">{char_count} character{char_count !== 1 ? 's' : ''}</span>}
    </>
};

export const Switch = (props) => {
    const ref = useRef(null);

    return <div class="switch">
        <input ref={ref} type="checkbox" {...props}/>
        <button type="button" class="slider" onClick={() => ref.current.click()}></button>
    </div>;
};

export const DropdownMenu = ({ content, className, options = [] }) => {
    const [ open, setOpen ] = useState(false);
    const dropdown_ref = useRef(null);
    const button_ref = useRef(null);

    useEffect(() => {
        let updateActiveDropdown = () => {
            const MARGIN = 4;

            if (!dropdown_ref.current || !button_ref.current) {
                return;
            }

            let btn_rect = button_ref.current.getBoundingClientRect();
            dropdown_ref.current.style.top = (btn_rect.top + btn_rect.height + MARGIN) + 'px';
            dropdown_ref.current.style.left = btn_rect.left + 'px';
            let dropdown_rect = dropdown_ref.current.getBoundingClientRect();

            if ((dropdown_rect.x + dropdown_rect.width) >= (window.innerWidth - MARGIN)) {
                dropdown_ref.current.style.left = ((btn_rect.x - dropdown_rect.width) + btn_rect.width) + 'px';
            }

            if (dropdown_rect.y + dropdown_rect.height >= (window.innerHeight - MARGIN)) {
                dropdown_ref.current.style.top = (btn_rect.y - dropdown_rect.height - MARGIN) + 'px';
            }
        };

        let handleClick = e => {
            if (!button_ref.current?.contains(e?.target)) {
                setOpen(false);
            }
        };

        document.addEventListener('scroll', updateActiveDropdown);
        window.addEventListener('resize', updateActiveDropdown);
        document.addEventListener('click', handleClick, true);
        updateActiveDropdown();

        return () => {
            window.removeEventListener('scroll', updateActiveDropdown);
            window.removeEventListener('resize', updateActiveDropdown);
            document.removeEventListener('click', handleClick, true);
        };
    }, [ open ]);

    return <div
        ref={button_ref}
        class={className}
        onClick={e => {
            e.stopPropagation();
            if (!dropdown_ref?.current?.contains(e.target)) {
                setOpen(!open);
            }
        }}
        dropdown
    >
        {content}
        <div ref={dropdown_ref} class="dropdown-menu" style={{ display: open ? 'flex' : 'none' }}>
            {options.filter(opt => opt.condition === undefined || opt.condition).map((opt, i) => <div
                key={i}
                class={opt.class}
                onClick={opt.onClick}
            >{opt.content}</div>)}
        </div>
    </div>;
};

export const formatDate = (timestamp, timezone, locale) => {
    return new Intl.DateTimeFormat(locale, {
        timeZone: timezone,
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(timestamp * 1000));
};

export const formatSize = (bytes) => {
    if (bytes === 0) {
        return '0B';
    }

    const factor = Math.floor((bytes.toString().length - 1) / 3);
    const size = bytes / Math.pow(1024, factor);

    return `${size.toFixed(2)}${[ 'B', 'kB', 'MB', 'GB', 'TB' ][factor] ?? ''}`;
};

export const getContentUrl = (path = '') => {
    const content_path = document.querySelector('meta[name="content_path"]')?.content || '/';
    return '/' + content_path + '/' + path.replace(/^\/+|\/+$/g, '');
};

export const ImageDialog = ({ onSave, onClose }) => {
    const user = useElement('/api/v2/me');
    const settings = useElement('/api/v2/settings');
    const [ path, setPath ] = useState('');
    const { data: files_req, is_loading, refetch: refetch_files } = useRequest({
        method: 'GET',
        url: `/api/v2/media?images=1&path=${path}`,
    });
    const folders = path.split('/');
    const input_ref = useRef(null);

    const uploadFile = async (e) => {
        makeRequest({
            method: 'POST',
            url: `/api/v2/media?path=${path}`,
            data: {
                file: e.target.files[0],
            },
        }).finally(() => {
            refetch_files();
            input_ref.current.value = '';
        });
    };

    const ListingContent = () => {
        const files = files_req ? files_req.data?.data : [];

        if (is_loading) {
            return <svg class="loading-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" fill="none" strokeWidth="10" r="36" strokeDasharray="171 56"></circle></svg>;
        }

        return <>
            <div class="listing-row header">
                <div class="w100"></div>
                <div class="w20" title="Information">Information</div>
                <div class="w20" title="Last modification">Last modification</div>
            </div>
            {files.map(file => {
                const file_path = getContentUrl(file.path);
                return <div
                    class="listing-row file"
                    onClick={() => {
                        if (file.is_file) {
                            onSave(file.path);
                            onClose();
                        } else {
                            setPath(file.path);
                        }
                    }}
                >
                    <div class="w100 align-center">
                        {file.is_file
                            ? <a href={file_path} target="_blank" class="pointer" onClick={e => e.stopPropagation()}>
                                <img src={file_path}/>
                            </a>
                            : <div className="pointer custom-media folder">
                                <IconFolderFill/>
                            </div>}
                        <span class="file-name">{file.name}</span>
                    </div>
                    <div class="w20 file-info">
                        {file.is_file && <p>{formatSize(file.size)}</p>}
                        <p>{file.mime}</p>
                    </div>
                    <div class="w20">{formatDate(file.time, settings.timezone, settings.language)}</div>
                </div>;
            })}
            {files.length == 0 && <span class="empty">No items</span>}
        </>;
    };

    return createPortal(<div id="image-dialog" class="dialog image-dialog open">
        <div>
            <div class="top">
                <div class="title">
                    <h2>Image picker</h2>
                    <span onClick={() => onClose()}><IconX/></span>
                </div>
                <div class="header">
                    <div id="image-dialog-file-form">
                        <button type="button" class="light" onClick={() => { onSave(null); onClose(); }}>Remove image</button>
                        <button type="button" id="image-dialog-file-button" onClick={() => input_ref.current.click()} disabled={!user?.actions?.edit_media}><IconUploadFile/></button>
                        <input ref={input_ref} type="file" class="hidden" accept="image/*" onInput={uploadFile}/>
                    </div>
                </div>
            </div>
            <div id="image-dialog-listing" class="listing">
                <ListingContent/>
            </div>
            <div class="media-paths-container">
                <div class="media-paths">
                    {folders.map((folder, i) => <>
                        <div class="pointer" onClick={() => setPath(folders.slice(0, i + 1).join('/'))}>{i == 0 ? <IconHome/> : folder}</div>
                        <span>/</span>
                    </>)}
                </div>
            </div>
        </div>
    </div>, document.querySelector('body'));
};

export const downloadFile = (data, filename) => {
    const link = document.createElement('a');
    link.href = window.URL.createObjectURL(new Blob([ data ]));
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
};
