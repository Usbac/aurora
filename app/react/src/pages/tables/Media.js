import React from 'react';
import { Table } from '../../components/Table';
import { useNavigate, useOutletContext } from 'react-router-dom';
import { DropdownMenu, formatDate, formatSize, makeRequest } from '../../utils/utils';
import { IconFile, IconFolderFill, IconThreeDots, IconTrash } from '../../utils/icons';

export default function Media() {
    const { user } = useOutletContext();
    const navigate = useNavigate();

    return <div class="content">
        <Table
            url="/api/v2/media"
            title="Media"
            topOptions={[
                {
                    content: <><b>+</b>&nbsp;New</>,
                    condition: Boolean(user?.actions?.edit_media),
                    onClick: () => navigate('/console/media/edit'),
                },
            ]}
            rowOnClick={media => navigate(`/console/media/edit?id=${media.id}`)}
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
                    onClick: (file) => {
                        if (confirm('Are you sure you want to delete the selected files? This action cannot be undone.')) {
                            makeRequest({
                                method: 'DELETE',
                                url: '/api/v2/media',
                                data: { id: file.map(l => l.id) },
                            }).then(res => alert(res?.data?.success ? 'Done' : 'Error'));
                        }
                    },
                },
            ]}
            columns={[
                {
                    class: 'w100',
                    content: file => <>
                        {file.is_image && <a href={file.path} target="_blank" className="pointer">
                            <img src={file.path}/>
                        </a>}
                        {!file.is_image && file.is_file && <a href={file.path} target="_blank" className="pointer custom-media file">
                            <IconFile/>
                        </a>}
                        {!file.is_file && <a href={'/admin/media?path=' + encodeURIComponent(file.path)} className="pointer custom-media folder">
                            <IconFolderFill/>
                        </a>}
                        <span>{file.name}</span>
                    </>,
                },
                {
                    title: 'Slug',
                    class: 'w20 file-info',
                    content: file => <p>{file.is_file ? formatSize(file.size) : file.mime}</p>,
                },
                {
                    title: '',
                    class: 'w20',
                    content: file => <p>{formatDate(file.time)}</p>,
                },
                {
                    class: 'w10 row-actions',
                    content: tag => <DropdownMenu
                        content={<IconThreeDots/>}
                        className="three-dots"
                        options={[
                            {
                                class: 'danger',
                                condition: Boolean(user?.actions?.edit_media),
                                onClick: () => {
                                    if (confirm('Are you sure you want to delete the file? This action cannot be undone.')) {
                                        makeRequest({
                                            method: 'DELETE',
                                            url: '/api/v2/media',
                                            data: { id: tag.id },
                                        }).then(res => alert(res?.data?.success ? 'Done' : 'Error'));
                                    }
                                },
                                content: <><IconTrash/> Delete</>
                            },
                        ]}
                    />,
                },
            ]}
        />
    </div>
}