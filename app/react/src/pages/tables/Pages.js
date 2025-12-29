import React from 'react';
import { Table } from '../../components/Table';
import { useNavigate, useOutletContext } from 'react-router-dom';
import { DropdownMenu, formatDate, makeRequest } from '../../utils/utils';
import { IconEye, IconThreeDots, IconTrash } from '../../utils/icons';
import { useI18n } from '../../providers/I18nProvider';

export default function Pages() {
    const { user, settings } = useOutletContext();
    const navigate = useNavigate();
    const { t } = useI18n();

    return <div class="content">
        <Table
            url="/api/pages"
            title={t('pages')}
            topOptions={[
                {
                    content: <><b>+</b>&nbsp;{t('new')}</>,
                    condition: Boolean(user?.actions?.edit_pages),
                    onClick: () => navigate('/admin/pages/edit'),
                },
            ]}
            rowOnClick={page => navigate(`/admin/pages/edit?id=${page.id}`)}
            filters={{
                status: {
                    title: t('status'),
                    options: [
                        { key: '', title: t('all') },
                        { key: '1', title: t('published') },
                        { key: '0', title: t('draft') },
                    ],
                },
                order: {
                    title: t('sort_by'),
                    options: [
                        { key: 'title', title: t('title') },
                        { key: 'status', title: t('status') },
                        { key: 'slug', title: t('slug') },
                        { key: 'edited', title: t('edited') },
                        { key: 'views', title: t('no_views') },
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
                    condition: Boolean(user?.actions?.edit_pages),
                    onClick: (pages) => {
                        if (confirm(t('confirm_delete_selected_pages'))) {
                            makeRequest({
                                method: 'DELETE',
                                url: '/api/pages',
                                data: { id: pages.map(l => l.id) },
                            }).then(res => alert(res?.data?.success ? t('pages_deleted_successfully') : t('error_deleting_pages')));
                        }
                    },
                },
            ]}
            columns={[
                {
                    class: 'w100',
                    content: page => <h3>
                        {page.title}
                        {!page.status && <span class="title-label red">{t('draft')}</span>}
                    </h3>,
                },
                {
                    title: t('slug'),
                    class: 'w20',
                    content: page => '/' + page.slug,
                },
                {
                    title: t('edited'),
                    class: 'w20',
                    content: page => formatDate(page.edited_at),
                },
                {
                    title: t('no_views'),
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
                                content: <><IconEye/> {t('view')}</>
                            },
                            {
                                class: 'danger',
                                condition: Boolean(user?.actions?.edit_pages),
                                onClick: () => {
                                    if (confirm(t('confirm_delete_page'))) {
                                        makeRequest({
                                            method: 'DELETE',
                                            url: '/api/pages',
                                            data: { id: page.id },
                                        }).then(res => alert(res?.data?.success ? t('page_deleted_successfully') : t('error_deleting_page')));
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