import React from 'react';
import { Table } from '../../components/Table';
import { useNavigate, useOutletContext } from 'react-router-dom';
import { DropdownMenu, makeRequest } from '../../utils/utils';
import { IconEye, IconThreeDots, IconTrash } from '../../utils/icons';
import { useI18n } from '../../providers/I18nProvider';

export default function Links() {
    const { user } = useOutletContext();
    const navigate = useNavigate();
    const { t } = useI18n();

    return <div class="content">
        <Table
            url="/api/links"
            title={t('links')}
            topOptions={[
                {
                    content: <><b>+</b>&nbsp;{t('new')}</>,
                    condition: Boolean(user?.actions?.edit_links),
                    onClick: () => navigate('/admin/links/edit'),
                },
            ]}
            rowOnClick={link => navigate(`/admin/links/edit?id=${link.id}`)}
            filters={{
                status: {
                    title: t('status'),
                    options: [
                        { key: '', title: t('all') },
                        { key: '1', title: t('active') },
                        { key: '0', title: t('inactive') },
                    ],
                },
                order: {
                    title: t('sort_by'),
                    options: [
                        { key: 'title', title: t('title') },
                        { key: 'url', title: t('url') },
                        { key: 'status', title: t('status') },
                        { key: 'order', title: t('order') },
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
                    condition: Boolean(user?.actions?.edit_links),
                    onClick: (links) => {
                        if (confirm(t('confirm_delete_selected_links'))) {
                            makeRequest({
                                method: 'DELETE',
                                url: '/api/links',
                                data: { id: links.map(l => l.id) },
                            }).then(res => alert(res?.data?.success ? t('links_deleted_successfully') : t('error_deleting_links')));
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
                    title: t('url'),
                    class: 'w20',
                    content: link => link.url,
                },
                {
                    title: t('status'),
                    class: 'w20',
                    content: link => <span class={`title-label ${link.status == 1 ? 'green' : 'red'}`}>{link.status == 1 ? t('active') : t('inactive')}</span>,
                },
                {
                    title: t('order'),
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
                                content: <><IconEye/> {t('view')}</>
                            },
                            {
                                class: 'danger',
                                condition: Boolean(user?.actions?.edit_links),
                                onClick: () => {
                                    if (confirm(t('confirm_delete_link'))) {
                                        makeRequest({
                                            method: 'DELETE',
                                            url: '/api/links',
                                            data: { id: link.id },
                                        }).then(res => alert(res?.data?.success ? t('link_deleted_successfully') : t('error_deleting_link')));
                                    }
                                },
                                content: <><IconTrash/> {t('delete')}</>
                            },
                        ]}
                    />,
                },
            ]}
        />
    </div>
}