import React, { useEffect, useState } from 'react';
import { Table } from '../components/Table';
import { useNavigate } from 'react-router-dom';

export default function Links() {
    const navigate = useNavigate();
    return <div class="content">
        <Table
            url="/api/v2/links"
            title="Links"
            addLink="/links/new"
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
            columns={[
                {
                    title: '',
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
                    title: '',
                    class: 'w10 row-actions',
                    content: link => <></>,
                },
            ]}
        />
    </div>
}