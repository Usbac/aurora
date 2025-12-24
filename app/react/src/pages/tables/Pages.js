import React from 'react';
import { Table } from '../../components/Table';
import { useNavigate, useOutletContext } from 'react-router-dom';
import { DropdownMenu, formatDate, makeRequest } from '../../utils/utils';
import { IconEye, IconThreeDots, IconTrash } from '../../utils/icons';

export default function Pages() {
    const { user, settings } = useOutletContext();
    const navigate = useNavigate();

    return <div class="content">
        <Table
            url="/api/pages"
            title="Pages"
            topOptions={[
                {
                    content: <><b>+</b>&nbsp;New</>,
                    condition: Boolean(user?.actions?.edit_pages),
                    onClick: () => navigate('/admin/pages/edit'),
                },
            ]}
            rowOnClick={page => navigate(`/admin/pages/edit?id=${page.id}`)}
            filters={{
                status: {
                    title: 'Status',
                    options: [
                        { key: '', title: 'All' },
                        { key: '1', title: 'Published' },
                        { key: '0', title: 'Draft' },
                    ],
                },
                order: {
                    title: 'Sort by',
                    options: [
                        { key: 'title', title: 'Title' },
                        { key: 'status', title: 'Status' },
                        { key: 'slug', title: 'Slug' },
                        { key: 'edited', title: 'Edited' },
                        { key: 'views', title: 'No. views' },
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
                    condition: Boolean(user?.actions?.edit_pages),
                    onClick: (pages) => {
                        if (confirm('Are you sure you want to delete the selected pages? This action cannot be undone.')) {
                            makeRequest({
                                method: 'DELETE',
                                url: '/api/pages',
                                data: { id: pages.map(l => l.id) },
                            }).then(res => alert(res?.data?.success ? 'Done' : 'Error'));
                        }
                    },
                },
            ]}
            columns={[
                {
                    class: 'w100',
                    content: page => <h3>
                        {page.title}
                        {!page.status && <span class="title-label red">Draft</span>}
                    </h3>,
                },
                {
                    title: 'Slug',
                    class: 'w20',
                    content: page => '/' + page.slug,
                },
                {
                    title: 'Edited',
                    class: 'w20',
                    content: page => formatDate(page.edited_at),
                },
                {
                    title: 'No. views',
                    class: 'w10 numeric',
                    condition: Boolean(settings.views_count),
                    content: page => page.views,
                },
                {
                    class: 'w10 row-actions',
                    content: page => <DropdownMenu
                        content={<IconThreeDots/>}
                        className="three-dots"
                        options={[
                            {
                                onClick: () => window.open(`/${page.slug}`, '_blank').focus(),
                                content: <><IconEye/> View</>
                            },
                            {
                                class: 'danger',
                                condition: Boolean(user?.actions?.edit_pages),
                                onClick: () => {
                                    if (confirm('Are you sure you want to delete the page? This action cannot be undone.')) {
                                        makeRequest({
                                            method: 'DELETE',
                                            url: '/api/pages',
                                            data: { id: page.id },
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