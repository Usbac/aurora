import React, { useEffect, useState } from 'react';
import { Table } from '../components/Table';
import { useNavigate, useOutletContext } from 'react-router-dom';
import { DropdownMenu, makeRequest } from '../utils/utils';
import { IconEye, IconThreeDots, IconTrash } from '../utils/icons';

export default function Links() {
    const { user } = useOutletContext();
    const navigate = useNavigate();

    return <div class="content">
        <Table
            url="/api/v2/links"
            title="Links"
            topOptions={[
                {
                    content: <><b>+</b>&nbsp;New</>,
                    onClick: () => navigate('/console/links/edit'),
                },
            ]}
            rowOnClick={link => navigate(`/console/links/edit?id=${link.id}`)}
            filters={{
                status: {
                    title: 'Status',
                    options: [
                        { key: '', title: 'All' },
                        { key: '1', title: 'Active' },
                        { key: '0', title: 'Inactive' },
                    ],
                },
                order: {
                    title: 'Sort by',
                    options: [
                        { key: 'title', title: 'Title' },
                        { key: 'url', title: 'URL' },
                        { key: 'status', title: 'Status' },
                        { key: 'order', title: 'Order' },
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
                    condition: Boolean(user?.actions?.edit_links),
                    onClick: (links) => {
                        if (confirm('Are you sure you want to delete the selected links? This action cannot be undone.')) {
                            makeRequest({
                                method: 'DELETE',
                                url: '/api/v2/links',
                                data: { id: links.map(l => l.id) },
                            }).then(res => alert(res?.data?.success ? 'Done' : 'Error'));
                        }
                    },
                },
            ]}
            columns={[
                {
                    class: 'w100',
                    content: link => <h3>{link.title}</h3>,
                },
                {
                    title: 'URL',
                    class: 'w20',
                    content: link => link.url,
                },
                {
                    title: 'Status',
                    class: 'w20',
                    content: link => <span class={`title-label ${link.status == 1 ? 'green' : 'red'}`}>{link.status == 1 ? 'Active' : 'Inactive'}</span>,
                },
                {
                    title: 'Order',
                    class: 'w10 numeric',
                    content: link => link.order,
                },
                {
                    class: 'w10 row-actions',
                    content: link => <DropdownMenu
                        content={<IconThreeDots/>}
                        className="three-dots"
                        options={[
                            {
                                onClick: () => window.open(link.url, '_blank').focus(),
                                content: <><IconEye/> View</>
                            },
                            {
                                class: 'danger',
                                condition: Boolean(user?.actions?.edit_links),
                                onClick: () => {
                    
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