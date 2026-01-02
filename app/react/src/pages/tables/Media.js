import React, { useEffect, useRef, useState } from 'react';
import { Table } from '../../utils/Table';
import { useOutletContext, useSearchParams } from 'react-router-dom';
import { downloadFile, DropdownMenu, formatDate, formatSize, getContentUrl, makeRequest } from '../../utils/utils';
import { IconClipboard, IconDuplicate, IconFile, IconFolder, IconFolderFill, IconHome, IconMoveFile, IconPencil, IconThreeDots, IconTrash, IconX, IconZip } from '../../utils/icons';
import { createPortal } from 'react-dom';
import { useI18n } from '../../providers/I18nProvider';

const MediaPath = ({ path, setPath }) => {
    const paths = path.split('/');

    return <div className="media-paths">
        {paths.map((folder, i) => {
            const folder_path = paths.slice(0, i + 1).join('/');
            
            return <>
                <div onClick={() => setPath(folder_path)} className="pointer">{i == 0 ? <IconHome/> : folder}</div>
                <span>/</span>
            </>;
        })}
    </div>;
};

const DialogEditFile = ({ file, onClose, onSuccess }) => {
    const [ name, setName ] = useState(file.name);
    const { t } = useI18n();

    const save = () => {
        makeRequest({
            method: 'POST',
            url: '/api/media/rename',
            data: {
                name: name,
                path: getContentUrl(file.path),
            },
        }).then(res => {
            alert(t(res?.data?.success ? 'item_renamed_successfully' : 'error_renaming_item'));
            if (res?.data?.success && onSuccess) {
                onSuccess();
            }
            onClose();
        });
    };

    return createPortal(<div className="dialog open">
        <div>
            <div className="top">
                <div className="title">
                    <h2>{t('rename')}</h2>
                    <span onClick={onClose}>
                        <IconX/>
                    </span>
                </div>
            </div>
            <div className="content input-group">
                <label htmlFor="file-name-input">{t('name')}</label>
                <input id="file-name-input" type="text" name="name" value={name} onChange={e => setName(e.target.value)}/>
            </div>
            <div className="bottom">
                <button className="light" onClick={onClose}>{t('cancel')}</button>
                <button onClick={save}>{t('save')}</button>
            </div>
        </div>
    </div>, document.body);
};

const DialogDuplicate = ({ file, onClose, onSuccess }) => {
    const [ name, setName ] = useState(file.name);
    const { t } = useI18n();

    const save = () => {
        makeRequest({
            method: 'POST',
            url: '/api/media/duplicate',
            data: {
                name: name,
                path: getContentUrl(file.path),
            },
        }).then(res => {
            alert(t(res?.data?.success ? 'item_duplicated_successfully' : 'error_duplicating_item'));
            if (res?.data?.success && onSuccess) {
                onSuccess();
            }
            onClose();
        });
    };

    return createPortal(<div className="dialog open">
        <div>
            <div className="top">
                <div className="title">
                    <h2>{t('duplicate')}</h2>
                    <span onClick={onClose}>
                        <IconX/>
                    </span>
                </div>
            </div>
            <div className="content input-group">
                <label htmlFor="file-name-input">{t('name')}</label>
                <input id="file-name-input" type="text" name="name" value={name} onChange={e => setName(e.target.value)}/>
            </div>
            <div className="bottom">
                <button className="light" onClick={onClose}>{t('cancel')}</button>
                <button onClick={save}>{t('save')}</button>
            </div>
        </div>
    </div>, document.body);
};

const DialogMove = ({ file, onClose, onSuccess }) => {
    const [ folders, setFolders ] = useState(undefined);
    const initial_destination_folder = file.path.slice(0, file.path.lastIndexOf('/')).replace(/^\/+|\/+$/g, '');
    const [ destination_folder, setDestinationFolder ] = useState(initial_destination_folder.length == 0 ? '/' : initial_destination_folder);
    const { t } = useI18n();

    useEffect(() => {
        makeRequest({
            method: 'GET',
            url: '/api/media/folders',
        }).then(res => setFolders(res?.data));
    }, []);

    const save = () => {
        makeRequest({
            method: 'POST',
            url: '/api/media/move',
            data: {
                name: getContentUrl(destination_folder),
                path: getContentUrl(file.path),
            },
        }).then(res => {
            alert(t(res?.data?.success ? 'item_moved_successfully' : 'error_moving_item'));
            if (res?.data?.success && onSuccess) {
                onSuccess();
            }
            onClose();
        });
    };

    return createPortal(<div className="dialog open">
        <div>
            <div className="top">
                <div className="title">
                    <h2>{t('move')}</h2>
                    <span onClick={onClose}>
                        <IconX/>
                    </span>
                </div>
            </div>
            <div className="content input-group">
                <label htmlFor="move-input">{t('folder')}</label>
                <select id="move-input" disabled={!folders} value={destination_folder} onChange={e => setDestinationFolder(e.target.value)}>
                    {folders?.map(folder => <option key={folder} value={folder}>{folder}</option>)}
                </select>
            </div>
            <div className="bottom">
                <button className="light" onClick={onClose}>{t('cancel')}</button>
                <button onClick={save}>{t('save')}</button>
            </div>
        </div>
    </div>, document.body);
};

const DialogCreateFolder = ({ path, onClose, onSuccess }) => {
    const [ name, setName ] = useState('');
    const { t } = useI18n();

    const save = () => {
        makeRequest({
            method: 'POST',
            url: '/api/media/create_folder',
            data: { name: path + '/' + name },
        }).then(res => {
            alert(t(res?.data?.success ? 'folder_created_successfully' : 'error_creating_folder'));
            if (res?.data?.success && onSuccess) {
                onSuccess();
            }
            onClose();
        });
    };

    return createPortal(<div className="dialog open">
        <div>
            <div className="top">
                <div className="title">
                    <h2>{t('create_folder')}</h2>
                    <span onClick={onClose}>
                        <IconX/>
                    </span>
                </div>
            </div>
            <div className="content input-group">
                <label htmlFor="file-name-input">{t('name')}</label>
                <input id="file-name-input" type="text" name="name" value={name} onChange={e => setName(e.target.value)}/>
            </div>
            <div className="bottom">
                <button className="light" onClick={onClose}>{t('cancel')}</button>
                <button onClick={save}>{t('save')}</button>
            </div>
        </div>
    </div>, document.body);
};

export default function Media() {
    const { user } = useOutletContext();
    const [ search_params, setSearchParams ] = useSearchParams();
    const [ current_dialog, setCurrentDialog ] = useState(null);
    const [ current_file, setCurrentFile ] = useState(null);
    const file_input_ref = useRef(null);
    const table_ref = useRef(null);
    const current_path = search_params.get('path') || '';
    const { t } = useI18n();

    const setPath = (new_path) => setSearchParams({ ...search_params, path: new_path });

    const deleteFile = (file) => {
        if (confirm(t('confirm_delete_file'))) {
            makeRequest({
                method: 'DELETE',
                url: '/api/media',
                data: [ getContentUrl(file.path) ],
            }).then(res => {
                alert(t(res?.data?.success ? 'file_deleted_successfully' : 'error_deleting_file'));
                if (res?.data?.success) {
                    table_ref?.current?.refetch();
                }
            });
        }
    };

    const copyPath = (path) => {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(path).then(() => alert(t('path_copied_to_clipboard')));
            return;
        }

        let input = document.createElement('input');
        input.setAttribute('value', path);
        document.body.appendChild(input);
        input.select();
        let result = document.execCommand('copy');
        document.body.removeChild(input);
        if (result) {
            alert(t('path_copied_to_clipboard'));
        }
    };

    const uploadFiles = async (e) => {
        const files = e.target.files;

        if (!files.length) {
            return;
        }

        const form_data = new FormData();

        for (let i = 0; i < files.length; i++) {
            form_data.append('file[]', files[i]);
        }

        makeRequest({
            method: 'POST',
            url: '/api/media/upload?path=' + encodeURIComponent(current_path),
            data: form_data,
        }).then(res => {
            alert(t(res?.data?.success ? 'files_uploaded_successfully' : 'error_uploading_files'));
            if (res?.data?.success) {
                table_ref?.current?.refetch();
            }
        });

        e.target.value = null;
    };

    const downloadFiles = () => {
        if (confirm(t('confirm_download_media'))) {
            makeRequest({
                method: 'GET',
                url: '/api/media/download?path=' + current_path,
                options: { responseType: 'blob' },
            }).then(res => {
                downloadFile(res.data, current_path + ' ' + new Date().toISOString().slice(0, 19).replace('T', ' ') + '.zip')
            });
        }
    };

    const openDialog = (dialog, file = null) => {
        setCurrentFile(file);
        setCurrentDialog(dialog);
    };

    const closeDialog = () => setCurrentDialog(null);

    const refreshTable = () => {
        table_ref?.current?.refetch();
    };

    return <div className="content">
        <Table
            ref={table_ref}
            url={`/api/media?path=${encodeURIComponent(current_path)}`}
            title={t('media')}
            topOptions={[
                {
                    content: <IconZip/>,
                    onClick: downloadFiles,
                },
                {
                    content: <IconFolder/>,
                    onClick: () => openDialog('create_folder'),
                },
                {
                    content: <>
                        <b>+</b>&nbsp;{t('new')}
                        <input ref={file_input_ref} type="file" multiple onChange={uploadFiles} style={{ display: 'none' }}/>
                    </>,
                    condition: Boolean(user?.actions?.edit_media),
                    onClick: () => file_input_ref?.current?.click(),
                },
            ]}
            filters={{
                order: {
                    title: t('sort_by'),
                    options: [
                        { key: 'type', title: t('type') },
                        { key: 'name', title: t('name') },
                        { key: 'size', title: t('size') },
                    ],
                },
                sort: {
                    options: [
                        { key: 'asc', title: t('ascending') },
                        { key: 'desc', title: t('descending') },
                    ],
                },
            }}
            options={[
                {
                    title: t('delete'),
                    class: 'danger',
                    condition: Boolean(user?.actions?.edit_media),
                    onClick: (files) => {
                        if (confirm(t('confirm_delete_selected_files'))) {
                            makeRequest({
                                method: 'DELETE',
                                url: '/api/media',
                                data: files.map(f => getContentUrl(f.path)),
                            }).then(res => {
                                alert(t(res?.data?.success ? 'files_deleted_successfully' : 'error_deleting_files'));
                                if (res?.data?.success) {
                                    table_ref?.current?.refetch();
                                }
                            });
                        }
                    },
                },
            ]}
            columns={[
                {
                    class: 'w100 align-center',
                    content: file => <>
                        {file.is_image && <a href={getContentUrl(file.path)} target="_blank" className="pointer">
                            <img src={getContentUrl(file.path)} className="row-thumb"/>
                        </a>}
                        {!file.is_image && file.is_file && <a href={getContentUrl(file.path)} target="_blank" className="pointer custom-media file">
                            <IconFile/>
                        </a>}
                        {!file.is_file && <div onClick={() => setPath(file.path)} className="pointer custom-media folder">
                            <IconFolderFill/>
                        </div>}
                        <span>{file.name}</span>
                    </>,
                },
                {
                    title: t('information'),
                    class: 'w20 file-info',
                    content: file => <p>{file.is_file ? formatSize(file.size) : file.mime}</p>,
                },
                {
                    title: t('last_modification'),
                    class: 'w20',
                    content: file => <p>{formatDate(file.time)}</p>,
                },
                {
                    class: 'w10 row-actions',
                    content: file => <DropdownMenu
                        content={<IconThreeDots/>}
                        className="three-dots"
                        options={[
                            {
                                onClick: () => copyPath(getContentUrl(file.path)),
                                content: <><IconClipboard/> {t('copy_path')}</>,
                            },
                            {
                                condition: Boolean(user?.actions?.edit_media),
                                onClick: () => openDialog('duplicate_file', file),
                                content: <><IconDuplicate/> {t('duplicate')}</>
                            },
                            {
                                condition: Boolean(user?.actions?.edit_media),
                                onClick: () => openDialog('move_file', file),
                                content: <><IconMoveFile/> {t('move')}</>
                            },
                            {
                                condition: Boolean(user?.actions?.edit_media),
                                onClick: () => openDialog('edit_file', file),
                                content: <><IconPencil/> {t('rename')}</>
                            },
                            {
                                class: 'danger',
                                condition: Boolean(user?.actions?.edit_media),
                                onClick: () => deleteFile(file),
                                content: <><IconTrash/> {t('delete')}</>
                            },
                        ]}
                    />,
                },
            ]}
        />
        {current_dialog == 'duplicate_file' && <DialogDuplicate file={current_file} onClose={closeDialog} onSuccess={refreshTable}/>}
        {current_dialog == 'move_file' && <DialogMove file={current_file} onClose={closeDialog} onSuccess={refreshTable}/>}
        {current_dialog == 'edit_file' && <DialogEditFile file={current_file} onClose={closeDialog} onSuccess={refreshTable}/>}
        {current_dialog == 'create_folder' && <DialogCreateFolder path={current_path} onClose={closeDialog} onSuccess={refreshTable}/>}
        <MediaPath path={search_params.get('path') || ''} setPath={setPath}/>
    </div>
}