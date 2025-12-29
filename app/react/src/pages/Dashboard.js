import React, { useEffect } from 'react';
import { getContentUrl, LoadingPage, MenuButton, useRequest } from '../utils/utils';
import { IconBook, IconPencil, IconTag, IconUser } from '../utils/icons';
import { useOutletContext } from 'react-router-dom';
import { useI18n } from '../providers/I18nProvider';

export default function Dashboard() {
    const { settings } = useOutletContext();
    const { t } = useI18n();
    const { data: links_req, is_loading: is_loading_links, fetch: fetch_links } = useRequest({
        method: 'GET',
        url: '/api/links',
    });
    const { data: posts_req, is_loading: is_loading_posts, fetch: fetch_posts } = useRequest({
        method: 'GET',
        url: '/api/posts?limit=6&status=1&order=published_at&sort=desc',
    });
    const { data: stats_req, is_loading: is_loading_stats, fetch: fetch_stats } = useRequest({
        method: 'GET',
        url: '/api/stats',
    });
    const links = links_req ? links_req.data?.data : null;
    const posts = posts_req ? posts_req.data?.data : null;
    const stats = stats_req ? stats_req.data : null;

    useEffect(() => {
        fetch_links();
        fetch_posts();
        fetch_stats();
    }, []);

    if (is_loading_links || is_loading_posts || is_loading_stats) {
        return <LoadingPage/>;
    }

    return (<div className="content">
        <div>
            <div className="page-title">
                <MenuButton/>
                <h2>{t('dashboard')}</h2>
            </div>
        </div>
        <div class="grid">
            <div class="grid grid-two-columns">
                <div class="grid">
                    {links && <div class="card dashboard v-spacing">
                        <h3>{t('links')}</h3>
                        <div class="dashboard-card-rows links">
                            {links.map(link => <a href={link.url} target="_blank" key={link.id}>{link.title}</a>)}
                        </div>
                    </div>}
                    <div class="card dashboard v-spacing">
                        <h3>{t('latest_published_posts')}</h3>
                        <div class="dashboard-card-rows">
                            {posts && posts.length > 0 && <>
                                {posts.map(post => <a href={`/${settings.blog_url}/${post.slug}`} target="_blank">
                                    <img src={post.image ? getContentUrl(post.image) : ''} alt={post.title} style={{ visibility: post.image ? 'initial' : 'hidden' }}/>
                                    <div>
                                        <b>{post.title}</b>
                                        <span class="subtitle">{post.user_id ? `${t('by')} ${post.user_name}` : <>&nbsp;</>}</span>
                                    </div>
                                </a>)}
                            </>}
                            {posts && posts.length == 0 && <span class="empty">{t('no_results')}</span>}
                        </div>
                    </div>
                </div>
                <div class="grid">
                    <div class="card dashboard v-spacing">
                        <h3>{t('start_creating')}</h3>
                        <div class="start-creating">
                            <a href="/admin/pages/edit"><IconBook/> <span>{t('create_page')}</span></a>
                            <a href="/admin/posts/edit"><IconPencil/> <span>{t('write_post')}</span></a>
                            <a href="/admin/users/edit"><IconUser/> <span>{t('add_user')}</span></a>
                            <a href="/admin/tags/edit"><IconTag/> <span>{t('add_tag')}</span></a>
                        </div>
                    </div>
                    <div class="card dashboard v-spacing">
                        <h3>{t('statistics')}</h3>
                        <div>
                            <div class="input-group">
                                <b>{t('posts')}</b>
                                <span>{stats.total_posts} {t('published')}, {stats.total_scheduled_posts} {t('scheduled')}, {stats.total_draft_posts} {t('draft')}</span>
                            </div>
                            <div class="input-group">
                                <b>{t('pages')}</b>
                                <span>{stats.total_pages} {t('published')}, {stats.total_draft_pages} {t('draft')}</span>
                            </div>
                            <div class="input-group">
                                <b>{t('users')}</b>
                                <span>{stats.total_users} {t('active')}, {stats.total_inactive_users} {t('inactive')}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>);
}