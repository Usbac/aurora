import React, { useCallback, useEffect, useRef, useState } from 'react';
import { IconFolderFill, IconHome, IconSpinner, IconUploadFile, IconX } from './icons';
import { createPortal } from 'react-dom';
import { Editor as TinyMCE } from '@tinymce/tinymce-react';
import { useI18n } from '../providers/I18nProvider';

/**
 * Performs an HTTP request using fetch with built-in handling for:
 * - query parameters for GET/HEAD requests
 * - JSON serialization for request bodies
 * - FormData support
 * - response parsing (json, text, blob)
 * @param {Object} params
 * @param {string} [params.method='GET'] - HTTP method (GET, POST, PUT, DELETE, etc.)
 * @param {string} params.url - Request URL
 * @param {Object|FormData} [params.data={}] - Data to send (query params or request body)
 * @param {Object} [params.options={}] - Additional fetch options
 * @param {'json'|'text'|'blob'} [params.options.response_type='json'] - Expected response type
 * @param {Object} [params.options.headers={}] - Custom request headers
 * @param {Object} [params.options.*] - Other fetch-compatible options (mode, signal, etc.)
 * @returns {Promise<{ data: any, status: number, statusText: string }>}
 * @throws {Error} Throws if the HTTP response is not OK (status >= 400).
 * The error includes `error.response.status`.
 * @example
 * const res = await makeRequest({
 *   method: 'POST',
 *   url: '/api/users',
 *   data: { name: 'John' }
 * });
 * @example
 * const res = await makeRequest({
 *   url: '/api/users',
 *   data: { page: 1 }
 * });
*/
const makeRequest = async ({ method = 'GET', url, data = {}, options = {} }) => {
    const {
        response_type,
        headers = {},
        ...fetch_options
    } = options;
    const request_headers = new Headers(headers);
    const http_method = (method || 'GET').toString().toUpperCase();
    let final_url = url;
    let body;

    if (data instanceof FormData) {
        body = data;
    } else if (http_method === 'GET' || http_method === 'HEAD') {
        const query_params = new URLSearchParams();
        if (data && typeof data === 'object') {
            for (const [ key, value ] of Object.entries(data)) {
                if (value !== undefined && value !== null) {
                    query_params.append(key, String(value));
                }
            }
        }
        const query_string = query_params.toString();
        if (query_string) {
            final_url += (final_url.includes('?') ? '&' : '?') + query_string;
        }
    } else {
        if (!request_headers.has('Content-Type')) {
            request_headers.set('Content-Type', 'application/json');
        }
        body = JSON.stringify(data ?? {});
    }

    const http_response = await fetch(final_url, {
        method: http_method,
        credentials: 'include',
        headers: request_headers,
        body,
        ...fetch_options,
    });

    if (!http_response.ok) {
        const error = new Error(`HTTP ${http_response.status}`);
        error.response = { status: http_response.status };
        throw error;
    }

    let parsed_body;
    switch (response_type) {
        case 'blob':
            parsed_body = await http_response.blob();
            break;
        case 'text':
            parsed_body = await http_response.text();
            break;
        default:
            const response_text = await http_response.text();
            parsed_body = response_text ? JSON.parse(response_text) : null;
            break;
    }

    return {
        data: parsed_body,
        status: http_response.status,
        statusText: http_response.statusText,
    };
}

/**
 * React hook that wraps {@link makeRequest} as `request` and shows translated alerts on failure (403 vs generic).
 * Must run under `I18nProvider` so `useI18n()` resolves.
 * @returns {{ request: (params: Object) => Promise<{ data: *, status: number, statusText: string }> }}
 *   The `request` function delegates to {@link makeRequest}; on rejection it `alert`s and rethrows.
 */
export const useApi = () => {
    const { t } = useI18n();

    const request = useCallback(async (params) => {
        try {
            return await makeRequest(params);
        } catch (err) {
            alert(t(err.response?.status === 403 ? 'forbidden_action' : 'error_generic'));
            throw err;
        }
    }, [ t ]);

    return { request };
}

/**
 * Fetches data with {@link makeRequest} on demand; exposes loading and error state (no global alerts).
 * @param {Object} params - The same shape as {@link makeRequest} (`method`, `url`, `data`, `options`).
 * @returns {{
 *   data: { data: *, status: number, statusText: string } | null,
 *   is_loading: boolean,
 *   is_error: boolean,
 *   fetch: () => Promise<void>
 * }}
 * Call `fetch()` to run or retry the request. `data` is the last successful envelope, or `null`.
 */
export const useRequest = (params) => {
    const [ data, setData ] = useState(null);
    const [ is_loading, setIsLoading ] = useState(true);
    const [ is_error, setIsError ] = useState(false);

    const fetch = useCallback(async () => {
        setIsLoading(true);
        setIsError(false);

        try {
            setData(await makeRequest(params));
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

/**
 * GETs a JSON URL once and returns `[ value, refetch ]` for simple read-only resources (e.g. `/api/me`).
 * @param {string} url - The request URL (GET, no body).
 * @returns {[*, () => Promise<void>]} Tuple: parsed `data` from the response body, or `undefined` while loading / on error; then a function to repeat the request.
 */
export const useElement = (url) => {
    const { data, is_loading, is_error, fetch } = useRequest({
        method: 'GET',
        url: url,
    });

    useEffect(() => {
        fetch();
    }, [ fetch ]);

    return [
        is_loading ? undefined : (data?.data && !is_error ? data.data : null),
        fetch,
    ];
};

/**
 * Renders the admin nav hamburger control; toggles `document.body` attribute `data-nav-open` on click.
 * @returns {React.ReactElement}
 */
export const MenuButton = () => <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" className="bi bi-list pointer" viewBox="0 0 16 16" onClick={() => document.body.toggleAttribute('data-nav-open')}>
    <path fillRule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
</svg>;

/**
 * Full-page centered spinner while async data is loading.
 * @returns {React.ReactElement}
 */
export const LoadingPage = () => <div className="content">
    <div className="loading-page">
        <IconSpinner className="loading-icon"/>
    </div>
</div>;

/**
 * Text input with optional character counter when `charCount` is set on props.
 * @param {React.InputHTMLAttributes<HTMLInputElement> & { charCount?: boolean }} props - Forwarded to `<input>`.
 * @returns {React.ReactElement}
 */
export const Input = (props) => {
    const char_count = props.value?.length || 0;

    return <>
        <input {...props}/>
        {props.charCount && <span className="char-counter">{char_count} character{char_count !== 1 ? 's' : ''}</span>}
    </>;
};

/**
 * Multiline input with optional character counter when `charCount` is set on props.
 * @param {React.TextareaHTMLAttributes<HTMLTextAreaElement> & { charCount?: boolean }} props - Forwarded to `<textarea>`.
 * @returns {React.ReactElement}
 */
export const Textarea = (props) => {
    const char_count = props.value?.length || 0;

    return <>
        <textarea {...props}></textarea>
        {props.charCount && <span className="char-counter">{char_count} character{char_count !== 1 ? 's' : ''}</span>}
    </>
};

/**
 * `<input type="datetime-local">` bound to a Unix timestamp (seconds) for `value` / `onChange`.
 * Remaining props are forwarded to {@link Input}.
 * @param {Object} props
 * @param {number|null|undefined} props.value - The time in **seconds** since epoch, or null/undefined.
 * @param {function (number|null): void} props.onChange - Called with seconds since epoch, or `null` when cleared.
 * @returns {React.ReactElement}
 */
export const DateTimeInput = ({ value, onChange, ...props }) => {
    let formatted_value = '';

    try {
        if (value != null && !isNaN(value)) {
            const date = new Date(value * 1000);
            if (!isNaN(date.getTime())) {
                formatted_value = date.toISOString().slice(0, 16);
            }
        }
    } catch {
        formatted_value = '';
    }
    
    return <Input
        type="datetime-local"
        value={formatted_value}
        onChange={e => {
            if (!e.target.value) {
                onChange(null); 
                return;
            }

            const new_date = new Date(e.target.value);
            if (!isNaN(new_date.getTime())) {
                onChange(Math.floor(new_date.getTime() / 1000));
            }
        }}
        {...props}
    />;
};

/**
 * Styled checkbox: native input plus a slider button that forwards clicks to the input.
 * @param {React.InputHTMLAttributes<HTMLInputElement>} props - Forwarded to the hidden `<input type="checkbox">`.
 * @returns {React.ReactElement}
 */
export const Switch = (props) => {
    const ref = useRef(null);

    return <div className="switch">
        <input ref={ref} type="checkbox" {...props}/>
        <button type="button" className="slider" onClick={() => ref.current.click()}></button>
    </div>;
};

/**
 * Generic dropdown: `trigger` toggles a fixed-position panel with `children` (viewport-clamped positioning).
 * @param {Object} props
 * @param {React.ReactNode} props.trigger - The visible clickable anchor.
 * @param {React.ReactNode} props.children - Content shown inside the floating panel when open.
 * @param {string} [props.className] - Extra classes on the trigger wrapper (`dropdown` base is always applied).
 * @param {string} [props.panelClassName] - Classes on the panel element (omit for unstyled panels; base layout uses inline `position`/`zIndex`).
 * @param {'left'|'right'|'center'} [props.align='right'] - Horizontal alignment vs the trigger: `auto` keeps previous behavior (left edge, then flip to align the panel’s right edge with the trigger if it overflows the viewport); `left` / `right` / `center` fix that relation and then clamp to the viewport.
 * @returns {React.ReactElement}
 */
export const Dropdown = ({ trigger, children, className, panelClassName, align = 'right' }) => {
    const [ open, setOpen ] = useState(false);
    const panel_ref = useRef(null);
    const anchor_ref = useRef(null);

    useEffect(() => {
        const update_position = () => {
            const MARGIN = 4;

            if (!panel_ref.current || !anchor_ref.current) {
                return;
            }

            const btn_rect = anchor_ref.current.getBoundingClientRect();
            panel_ref.current.style.top = (btn_rect.top + btn_rect.height + MARGIN) + 'px';
            panel_ref.current.style.left = btn_rect.left + 'px';

            const panel_rect_after_left = panel_ref.current.getBoundingClientRect();
            const panel_w = panel_rect_after_left.width;

            let ideal_left = 0;
            switch (align) {
                case 'right': ideal_left = btn_rect.right - panel_w; break;
                case 'center': ideal_left = btn_rect.left + btn_rect.width / 2 - panel_w / 2; break;
                case 'left': default: ideal_left = btn_rect.left; break;
            }

            const max_left = window.innerWidth - panel_w - MARGIN;
            panel_ref.current.style.left = Math.max(MARGIN, Math.min(ideal_left, max_left)) + 'px';
            const panel_rect = panel_ref.current.getBoundingClientRect();

            if (panel_rect.y + panel_rect.height >= (window.innerHeight - MARGIN)) {
                panel_ref.current.style.top = (btn_rect.y - panel_rect.height - MARGIN) + 'px';
            }
        };

        const handle_click = e => {
            if (!anchor_ref.current?.contains(e?.target)) {
                setOpen(false);
            }
        };

        document.addEventListener('scroll', update_position);
        window.addEventListener('resize', update_position);
        document.addEventListener('click', handle_click, true);
        update_position();

        return () => {
            document.removeEventListener('scroll', update_position);
            window.removeEventListener('resize', update_position);
            document.removeEventListener('click', handle_click, true);
        };
    }, [ open, align ]);

    return <div
        ref={anchor_ref}
        className={`dropdown ${className || ''}`}
        onClick={e => {
            e.stopPropagation();
            if (!panel_ref?.current?.contains(e.target)) {
                setOpen(!open);
            }
        }}
    >
        {trigger}
        <div
            ref={panel_ref}
            className={panelClassName}
            style={{
                display: open ? 'flex' : 'none',
                position: 'fixed',
                zIndex: 1,
            }}
        >
            {children}
        </div>
    </div>;
};

/**
 * Dropdown anchored to a trigger `content`.
 * @param {Object} props
 * @param {React.ReactNode} props.content - The visible trigger (e.g. icon).
 * @param {string} props.className - Extra class names on the wrapper.
 * @param {Array<{ content: React.ReactNode, onClick: function, class?: string, condition?: boolean }>} [props.options=[]] - Menu rows; filtered by `condition` when present.
 * @param {'left'|'right'|'center'} [props.align] - Passed through to {@link Dropdown}.
 * @returns {React.ReactElement}
 */
export const DropdownMenu = ({ content, className, options = [], align = 'right' }) => (
    <Dropdown trigger={content} className={className} panelClassName="dropdown-content dropdown-menu" align={align}>
        {options.filter(opt => opt.condition === undefined || opt.condition).map((opt, i) => (
            <div key={i} className={opt.class} onClick={opt.onClick}>{opt.content}</div>
        ))}
    </Dropdown>
);

/**
 * Formats a Unix timestamp (seconds) for a timezone and locale using `Intl.DateTimeFormat`.
 * @param {number} timestamp - The Unix time in **seconds**.
 * @param {string} timezone - The IANA time zone name (e.g. `Europe/Madrid`).
 * @param {string} locale - The BCP 47 locale tag.
 * @returns {string} The formatted date-time string.
 */
export const formatDate = (timestamp, timezone, locale) => {
    timestamp = Number(timestamp);

    return new Intl.DateTimeFormat(locale, {
        timeZone: timezone,
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date((Number.isFinite(timestamp) ? timestamp : 0) * 1000));
};

/**
 * Human-readable byte size (B, kB, MB, GB, TB).
 * @param {number} bytes - The size in bytes.
 * @returns {string} The formatted label (e.g. `1.50MB`).
 */
export const formatSize = (bytes) => {
    if (bytes === 0) {
        return '0B';
    }

    const factor = Math.floor((bytes.toString().length - 1) / 3);
    const size = bytes / Math.pow(1024, factor);

    return `${size.toFixed(2)}${[ 'B', 'kB', 'MB', 'GB', 'TB' ][factor] ?? ''}`;
};

/**
 * Builds an absolute URL from the current origin and a path (no trailing slash on origin only).
 * @param {string} [path=''] - The path without leading slash, or empty for origin only.
 * @returns {string} The full URL.
 */
export const getUrl = (path = '') => {
    const { protocol, hostname, port } = window.location;
    const base = `${protocol}//${hostname}${port ? ':' + port : ''}`;
    path = path.replace(/^\/+/, '');

    return path ? `${base}/${path}` : base;
};

/**
 * Modal image picker (portal to `document.body`): browses `/api/media`, uploads files, calls `onSave` with a content path or `null`.
 * @param {Object} props
 * @param {function (string|null): void} props.onSave - Called with the selected file path, or `null` when removing the image.
 * @param {function (): void} props.onClose - Called to dismiss the dialog.
 * @returns {React.ReactElement}
 */
export const ImageDialog = ({ onSave, onClose }) => {
    const [ user ] = useElement('/api/me');
    const [ settings ] = useElement('/api/settings');
    const [ path, setPath ] = useState('');
    const { data: files_req, is_loading, fetch: fetch_files } = useRequest({
        method: 'GET',
        url: `/api/media?images=1&path=${path}`,
    });
    const folders = path.split('/');
    const input_ref = useRef(null);
    const { request } = useApi();

    useEffect(() => {
        fetch_files();
    }, [ path ]);

    /**
     * Uploads the first selected file to the current media path, then refreshes the listing.
     * @param {React.ChangeEvent<HTMLInputElement>} e - The file input change event.
     * @returns {Promise<void>}
     */
    const uploadFile = async (e) => {
        const form_data = new FormData();
        form_data.append('file', e.target.files[0]);
        request({
            method: 'POST',
            url: `/api/media?path=${path}`,
            data: form_data,
        }).finally(() => {
            fetch_files();
            input_ref.current.value = '';
        });
    };

    /**
     * Lists files in the current folder or a loading spinner.
     * @returns {React.ReactElement}
     */
    const ListingContent = () => {
        const files = files_req ? files_req.data?.data : [];

        if (is_loading || !user || !settings) {
            return <IconSpinner className="loading-icon"/>;
        }

        return <>
            <div className="listing-row header">
                <div className="w100"></div>
                <div className="w20" title="Information">Information</div>
                <div className="w20" title="Last modification">Last modification</div>
            </div>
            {files.map(file => {
                return <div
                    className="listing-row"
                    onClick={() => {
                        if (file.is_file) {
                            onSave(file.path);
                            onClose();
                        } else {
                            setPath(file.path);
                        }
                    }}
                >
                    <div className="w100 align-center">
                        {file.is_file
                            ? <a href={file.path} target="_blank" className="pointer" onClick={e => e.stopPropagation()}>
                                <img src={file.path} className="row-thumb"/>
                            </a>
                            : <div className="pointer custom-media folder">
                                <IconFolderFill className="row-thumb"/>
                            </div>}
                        <span className="file-name">{file.name}</span>
                    </div>
                    <div className="w20 file-info">
                        {file.is_file && <p>{formatSize(file.size)}</p>}
                        <p>{file.mime}</p>
                    </div>
                    <div className="w20">{formatDate(file.time, settings.timezone, settings.language)}</div>
                </div>;
            })}
            {files.length == 0 && <span className="empty">No items</span>}
        </>;
    };

    return createPortal(<div id="image-dialog" className="dialog image-dialog open">
        <div>
            <div className="top">
                <div className="title">
                    <h2>Image picker</h2>
                    <span onClick={() => onClose()}><IconX/></span>
                </div>
                <div className="header">
                    <div id="image-dialog-file-form">
                        <button type="button" className="light" onClick={() => { onSave(null); onClose(); }}>Remove image</button>
                        <button type="button" id="image-dialog-file-button" onClick={() => input_ref.current.click()} disabled={!user?.actions?.edit_media}><IconUploadFile/></button>
                        <input ref={input_ref} type="file" className="hidden" accept="image/*" onInput={uploadFile}/>
                    </div>
                </div>
            </div>
            <div id="image-dialog-listing" className="listing">
                <ListingContent/>
            </div>
            <div className="media-paths">
                {folders.map((folder, i) => <>
                    <div className="pointer" onClick={() => setPath(folders.slice(0, i + 1).join('/'))}>{i == 0 ? <IconHome/> : folder}</div>
                    <span>/</span>
                </>)}
            </div>
        </div>
    </div>, document.querySelector('body'));
};

/**
 * Triggers a browser download of raw `data` as `filename` via a temporary `<a download>`.
 * @param {BlobPart|BlobPart[]} data - The file contents (e.g. `ArrayBuffer` or `Uint8Array`).
 * @param {string} filename - The suggested download file name.
 * @returns {void}
 */
export const downloadFile = (data, filename) => {
    const link = document.createElement('a');
    link.href = window.URL.createObjectURL(new Blob([ data ]));
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
};

/**
 * Maps a stored role slug to a short English display title for the admin UI.
 * @param {string} role_slug - The role key (`contributor`, `editor`, `admin`, `owner`, …).
 * @returns {string} The display label, or an empty string if unknown.
 */
export const getRoleTitle = (role_slug) => {
    switch (role_slug) {
        case 'contributor': return 'Contributor';
        case 'editor': return 'Editor';
        case 'admin': return 'Administrator';
        case 'owner': return 'Owner';
        default: return '';
    }
};

/**
 * Normalizes a string into a URL slug (lowercase, non-alphanumeric stripped, spaces to hyphens).
 * @param {string} str - The raw title or name.
 * @returns {string} The slug.
 */
export const getSlug = (str) => str.toLowerCase().replace(/[^\w ]+/g, '').replace(/ +/g, '-');

/**
 * TinyMCE rich text editor with Aurora toolbar, image upload, and light/dark skin from `theme`.
 * @param {Object} props
 * @param {string} props.value - The HTML content.
 * @param {function (string): void} props.setValue - Called when the editor content changes.
 * @param {'light'|'dark'} props.theme - Chooses TinyMCE `oxide` vs `oxide-dark` skin.
 * @returns {React.ReactElement}
 */
export const Editor = ({ value, setValue, theme }) => {
    return <TinyMCE
        licenseKey="gpl"
        tinymceScriptSrc="/public/assets/js/tinymce/tinymce.min.js"
        value={value}
        init={{
            menubar: false,
            plugins: [ 'image', 'wordcount', 'autoresize', 'code', 'link', 'lists' ],
            toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image code',
            images_upload_url: '/api/media/upload_image',
            skin: theme === 'dark' ? 'oxide-dark' : 'oxide',
            content_css: theme === 'dark' ? 'dark' : 'default',
            setup: editor => {
                editor.on('Change Keyup', () => {
                    setValue(editor.getContent());
                });
            },
        }}
    />;
};

/**
 * Returns the OS and version based on the user agent.
 * @param {string} user_agent The user agent.
 * @returns {{ os: string, version: string }} The OS and version.
 */
export const getDeviceInfo = (user_agent) => {
    const ua = (user_agent || '').trim();

    if (!ua) {
        return { os: 'Unknown', version: '' };
    }

    // Chrome OS
    const chrome = ua.match(/CrOS [^ ]+ ([\d.]+)/i);
    if (chrome) {
        return { os: 'Chrome OS', version: chrome[1] };
    }

    // Windows
    const windows = ua.match(/Windows NT ([\d.]+)/i);
    if (windows) {
        return {
            os: 'Windows',
            version: {
                '10.0': '10/11',
                '6.3': '8.1',
                '6.2': '8',
                '6.1': '7',
                '6.0': 'Vista',
                '5.2': 'XP x64',
                '5.1': 'XP',
                '5.0': '2000',
            }[windows[1]] ?? windows[1],
        };
    }

    if (/Windows Phone/i.test(ua)) {
        const wp = ua.match(/Windows Phone(?: OS)? ([\d.]+)/i);
        return { os: 'Windows Phone', version: wp ? wp[1] : '' };
    }

    // Android
    const android = ua.match(/Android ([\d.]+)/i);
    if (android) {
        return { os: 'Android', version: android[1] };
    }

    // iOS / iPadOS (WebKit clients)
    const ios = ua.match(/(?:CPU (?:iPhone )?OS|CPU OS) ([\d_]+)/i);
    if (ios) {
        const ver = ios[1].replace(/_/g, '.');
        if (/iPad/i.test(ua)) {
            return { os: 'iPadOS', version: ver };
        }

        if (/iPhone|iPod/i.test(ua)) {
            return { os: 'iOS', version: ver };
        }
    }

    // macOS
    const mac = ua.match(/Mac OS X ([\d_]+)/i);
    if (mac) {
        return { os: 'macOS', version: mac[1].replace(/_/g, '.') };
    }

    // Linux / generic
    if (/Linux/i.test(ua)) {
        return { os: 'Linux', version: '' };
    }

    return { os: 'Unknown', version: '' };
};

/**
 * Returns the device type based on the user agent.
 * @param {String} user_agent The user agent.
 * @returns {String} The device type (Mobile, Tablet, Desktop).
 */
export const getDeviceType = (user_agent) => {
    if (/Mobi|Android/i.test(user_agent)) {
        return 'Mobile';
    }

    if (/Tablet|iPad/i.test(user_agent)) {
        return 'Tablet';
    }

    return 'Desktop';
};

/**
 * Returns a string with the specified character trimmed from the beginning and end.
 * @param {String} str The string to trim.
 * @param {String} char The character to trim.
 * @returns {String} The trimmed string.
 */
export const trimChar = (str, char) => {
    const escaped = char.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    return str.replace(new RegExp(`^${escaped}+|${escaped}+$`, 'g'), '');
}