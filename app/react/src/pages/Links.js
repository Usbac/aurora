import React, { useEffect, useState } from 'react';
import { Table } from '../components/Table';
import { useNavigate, useOutletContext } from 'react-router-dom';

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
                    onClick: () => navigate('/console/links/new'),
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
                    onClick: () => alert('Delete clicked'),
                },
            ]}
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
                    content: link => <></> /*<div class="three-dots" onclick="return false" dropdown>
                        <?= $this->include('icons/dots.svg') ?>
                        <div class="dropdown-menu">
                            <div onclick="window.open(<?= e(js($link['url'])) ?>, '_blank').focus()"><?= $this->include('icons/eye.svg') ?> <?= $this->t('view') ?></div>
                            <?php if (\Aurora\App\Permission::can('edit_links')): ?>
                                <div
                                    class="danger"
                                    onclick="confirm(LANG.delete_confirm.sprintf(<?= e(js($link['title'])) ?>)) && Form.send('/admin/links/remove', null, null, {
                                            csrf: csrf_token,
                                            id: <?= e(js($link['id'])) ?>,
                                        }).then(res => Listing.handleResponse(res));"
                                ><?= $this->include('icons/trash.svg') ?> <?= $this->t('delete') ?></div>
                            <?php endif ?>
                        </div>
                    </div>*/,
                },
            ]}
        />
    </div>
}