import React from 'react';
import { Table } from '../../components/Table';
import { useNavigate, useOutletContext } from 'react-router-dom';
import { DropdownMenu, makeRequest } from '../../utils/utils';
import { IconEye, IconThreeDots, IconTrash } from '../../utils/icons';
import { useI18n } from '../../providers/I18nProvider';

export default function Tags() {
    const { user, settings } = useOutletContext();
    const navigate = useNavigate();
    const { t } = useI18n();

    return <div class="content">
        <Table
            url="/api/tags"
            title={t('tags')}
            topOptions={[
                {
                    content: <><b>+</b>&nbsp;{t('new')}</>,
                    condition: Boolean(user?.actions?.edit_tags),
                    onClick: () => navigate('/admin/tags/edit'),
                },
            ]}
            rowOnClick={tag => navigate(`/admin/tags/edit?id=${tag.id}`)}
            filters={{
                order: {
                    title: t('sort_by'),
                    options: [
                        { key: 'name', title: t('name') },
                        { key: 'slug', title: t('slug') },
                        { key: 'posts', title: t('no_posts') },
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
                    condition: Boolean(user?.actions?.edit_tags),
                    onClick: (tags) => {
                        if (confirm(t('confirm_delete_selected_tags'))) {
                            makeRequest({
                                method: 'DELETE',
                                url: '/api/tags',
                                data: { id: tags.map(l => l.id) },
                            }).then(res => alert(t(res?.data?.success ? 'tags_deleted_successfully' : 'error_deleting_tags')));
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
                    title: t('slug'),
                    class: 'w30',
                    content: tag => tag.slug,
                },
                {
                    title: t('no_posts'),
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
                                content: <><IconEye/> {t('view')}</>
                            },
                            {
                                class: 'danger',
                                condition: Boolean(user?.actions?.edit_tags),
                                onClick: () => {
                                    if (confirm(t('confirm_delete_tag'))) {
                                        makeRequest({
                                            method: 'DELETE',
                                            url: '/api/tags',
                                            data: { id: tag.id },
                                        }).then(res => alert(t(res?.data?.success ? 'tag_deleted_successfully' : 'error_deleting_tag')));
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