import React from 'react';
import { Table } from '../../components/Table';
import { useNavigate, useOutletContext } from 'react-router-dom';
import { DropdownMenu, makeRequest } from '../../utils/utils';
import { IconEye, IconThreeDots, IconTrash } from '../../utils/icons';

export default function Tags() {
    const { user } = useOutletContext();
    const navigate = useNavigate();

    return <div class="content">
        <Table
            url="/api/v2/tags"
            title="Tags"
            topOptions={[
                {
                    content: <><b>+</b>&nbsp;New</>,
                    onClick: () => navigate('/console/tags/edit'),
                },
            ]}
            rowOnClick={tag => navigate(`/console/tags/edit?id=${tag.id}`)}
            filters={{
                order: {
                    title: 'Sort by',
                    options: [
                        { key: 'name', title: 'Name' },
                        { key: 'slug', title: 'Slug' },
                        { key: 'posts', title: 'No. posts' },
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
                    condition: Boolean(user?.actions?.edit_tags),
                    onClick: (tags) => {
                        if (confirm('Are you sure you want to delete the selected tags? This action cannot be undone.')) {
                            makeRequest({
                                method: 'DELETE',
                                url: '/api/v2/tags',
                                data: { id: tags.map(l => l.id) },
                            }).then(res => alert(res?.data?.success ? 'Done' : 'Error'));
                        }
                    },
                },
            ]}
            columns={[
                {
                    class: 'w100',
                    content: tag => <h3>{tag.name}</h3>,
                },
                {
                    title: 'Slug',
                    class: 'w30',
                    content: tag => tag.slug,
                },
                {
                    title: 'No. posts',
                    class: 'w10 numeric',
                    content: tag => tag.posts,
                },
                {
                    class: 'w10 row-actions',
                    content: tag => <DropdownMenu
                        content={<IconThreeDots/>}
                        className="three-dots"
                        options={[
                            {
                                onClick: () => window.open(`/${settings.blog_url}/tag/${tag.slug}`, '_blank').focus(),
                                content: <><IconEye/> View</>
                            },
                            {
                                class: 'danger',
                                condition: Boolean(user?.actions?.edit_tags),
                                onClick: () => {
                                    if (confirm('Are you sure you want to delete the tag? This action cannot be undone.')) {
                                        makeRequest({
                                            method: 'DELETE',
                                            url: '/api/v2/tags',
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