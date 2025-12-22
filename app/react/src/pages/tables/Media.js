import React, { useEffect, useState } from 'react';
import { Table } from '../../components/Table';
import { useNavigate, useOutletContext, useSearchParams } from 'react-router-dom';
import { DropdownMenu, formatDate, formatSize, getContentUrl, makeRequest } from '../../utils/utils';
import { IconDuplicate, IconFile, IconFolderFill, IconHome, IconMoveFile, IconPencil, IconThreeDots, IconTrash, IconX } from '../../utils/icons';
import { createPortal } from 'react-dom';

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

const DialogEditFile = ({ file, onClose }) => {
    const [ name, setName ] = useState(file.name);

    const save = () => {
        makeRequest({
            method: 'POST',
            url: '/api/v2/media/rename',
            data: {
                name: name,
                path: getContentUrl(file.path),
            },
        }).then(res => {
            alert(res?.data?.success ? 'Done' : 'Error renaming item. The name is invalid, the file does not comply with the server rules or the path is not writable.');
            onClose();
        });
    };

    return createPortal(<div className="dialog open">
        <div>
            <div className="top">
                <div className="title">
                    <h2>Rename</h2>
                    <span onClick={onClose}>
                        <IconX/>
                    </span>
                </div>
            </div>
            <div className="content input-group">
                <label htmlFor="file-name-input">Name</label>
                <input id="file-name-input" type="text" name="name" value={name} onChange={e => setName(e.target.value)}/>
            </div>
            <div className="bottom">
                <button className="light" onClick={onClose}>Cancel</button>
                <button onClick={save}>Save</button>
            </div>
        </div>
    </div>, document.body);
};

const DialogDuplicate = ({ file, onClose }) => {
    const [ name, setName ] = useState(file.name);

    const save = () => {
        makeRequest({
            method: 'POST',
            url: '/api/v2/media/duplicate',
            data: {
                name: name,
                path: getContentUrl(file.path),
            },
        }).then(res => {
            alert(res?.data?.success ? 'Done' : 'Error duplicating item. The name is invalid, the file does not comply with the server rules or the path is not writable.');
            onClose();
        });
    };

    return createPortal(<div className="dialog open">
        <div>
            <div className="top">
                <div className="title">
                    <h2>Duplicate</h2>
                    <span onClick={onClose}>
                        <IconX/>
                    </span>
                </div>
            </div>
            <div className="content input-group">
                <label htmlFor="file-name-input">Name</label>
                <input id="file-name-input" type="text" name="name" value={name} onChange={e => setName(e.target.value)}/>
            </div>
            <div className="bottom">
                <button className="light" onClick={onClose}>Cancel</button>
                <button onClick={save}>Save</button>
            </div>
        </div>
    </div>, document.body);
};

const DialogMove = ({ file, onClose }) => {
    const [ folders, setFolders ] = useState(undefined);
    const initial_destination_folder = file.path.slice(0, file.path.lastIndexOf('/')).replace(/^\/+|\/+$/g, '');
    const [ destination_folder, setDestinationFolder ] = useState(initial_destination_folder.length == 0 ? '/' : initial_destination_folder);

    useEffect(() => {
        makeRequest({
            method: 'GET',
            url: '/api/v2/media/folders',
        }).then(res => setFolders(res?.data));
    }, []);

    const save = () => {
        makeRequest({
            method: 'POST',
            url: '/api/v2/media/move',
            data: {
                name: getContentUrl(destination_folder),
                path: getContentUrl(file.path),
            },
        }).then(res => {
            alert(res?.data?.success ? 'Done' : 'Error');
            onClose();
        });
    };

    return createPortal(<div className="dialog open">
        <div>
            <div className="top">
                <div className="title">
                    <h2>Move</h2>
                    <span onClick={onClose}>
                        <IconX/>
                    </span>
                </div>
            </div>
            <div className="content input-group">
                <label htmlFor="move-input">Folder</label>
                <select id="move-input" disabled={!folders} value={destination_folder} onChange={e => setDestinationFolder(e.target.value)}>
                    {folders?.map(folder => <option key={folder} value={folder}>{folder}</option>)}
                </select>
            </div>
            <div className="bottom">
                <button className="light" onClick={onClose}>Cancel</button>
                <button onClick={save}>Save</button>
            </div>
        </div>
    </div>, document.body);
};

export default function Media() {
    const { user } = useOutletContext();
    const [ search_params, setSearchParams ] = useSearchParams();
    const [ current_dialog, setCurrentDialog ] = useState(null);
    const [ current_file, setCurrentFile ] = useState(null);
    const navigate = useNavigate();

    const setPath = (new_path) => setSearchParams({ ...search_params, path: new_path });

    const deleteFile = (file) => {
        if (confirm('Are you sure you want to delete the file? This action cannot be undone.')) {
            makeRequest({
                method: 'DELETE',
                url: '/api/v2/media',
                data: { id: file.id },
            }).then(res => alert(res?.data?.success ? 'Done' : 'Error'));
        }
    };

    const openDialog = (dialog, file) => {
        setCurrentFile(file);
        setCurrentDialog(dialog);
    };

    const closeDialog = () => setCurrentDialog(null);

    return <div className="content">
        <Table
            url={`/api/v2/media?path=${encodeURIComponent(search_params.get('path') || '')}`}
            title="Media"
            topOptions={[
                {
                    content: <><b>+</b>&nbsp;New</>,
                    condition: Boolean(user?.actions?.edit_media),
                    onClick: () => navigate('/console/media/edit'),
                },
            ]}
            filters={{
                order: {
                    title: 'Sort by',
                    options: [
                        { key: 'type', title: 'Type' },
                        { key: 'name', title: 'Name' },
                        { key: 'size', title: 'Size' },
                    ],
                },
                sort: {
                    options: [
                        { key: 'asc', title: 'Ascending' },
                        { key: 'desc', title: 'Descending' },
                    ],
                },
            }}
            options={[
                {
                    title: 'Delete',
                    class: 'danger',
                    condition: Boolean(user?.actions?.edit_media),
                    onClick: (files) => {
                        if (confirm('Are you sure you want to delete the selected files? This action cannot be undone.')) {
                            makeRequest({
                                method: 'DELETE',
                                url: '/api/v2/media',
                                data: { id: files.map(l => l.id) },
                            }).then(res => alert(res?.data?.success ? 'Done' : 'Error'));
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
                    title: 'Information',
                    class: 'w20 file-info',
                    content: file => <p>{file.is_file ? formatSize(file.size) : file.mime}</p>,
                },
                {
                    title: 'Last Modification',
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
                                condition: Boolean(user?.actions?.edit_media),
                                onClick: () => openDialog('duplicate_file', file),
                                content: <><IconDuplicate/> Duplicate</>
                            },
                            {
                                condition: Boolean(user?.actions?.edit_media),
                                onClick: () => openDialog('move_file', file),
                                content: <><IconMoveFile/> Move</>
                            },
                            {
                                condition: Boolean(user?.actions?.edit_media),
                                onClick: () => openDialog('edit_file', file),
                                content: <><IconPencil/> Rename</>
                            },
                            {
                                class: 'danger',
                                condition: Boolean(user?.actions?.edit_media),
                                onClick: () => deleteFile(file),
                                content: <><IconTrash/> Delete</>
                            },
                        ]}
                    />,
                },
            ]}
        />
        {current_dialog == 'duplicate_file' && <DialogDuplicate file={current_file} onClose={closeDialog}/>}
        {current_dialog == 'move_file' && <DialogMove file={current_file} onClose={closeDialog}/>}
        {current_dialog == 'edit_file' && <DialogEditFile file={current_file} onClose={closeDialog}/>}
        <MediaPath path={search_params.get('path') || ''} setPath={setPath}/>
    </div>
}