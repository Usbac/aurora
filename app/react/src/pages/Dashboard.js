import React, { useEffect } from 'react';
import { LoadingPage, MenuButton, useRequest } from '../utils/utils';
import { IconBook, IconPencil, IconTag, IconUser } from '../utils/icons';
import { useOutletContext } from 'react-router-dom';

export default function Dashboard() {
    const { settings } = useOutletContext();
    const { data: links_req, is_loading: is_loading_links, fetch: fetch_links } = useRequest({
        method: 'GET',
        url: '/api/v2/links',
    });
    const { data: posts_req, is_loading: is_loading_posts, fetch: fetch_posts } = useRequest({
        method: 'GET',
        url: '/api/v2/posts?limit=6&status=1&order=published_at&sort=desc',
    });
    const { data: stats_req, is_loading: is_loading_stats, fetch: fetch_stats } = useRequest({
        method: 'GET',
        url: '/api/v2/stats',
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
                <h2>Dashboard</h2>
            </div>
        </div>
        <div class="grid">
            <div class="grid grid-two-columns">
                <div class="grid">
                    {links && <div class="card dashboard v-spacing">
                        <h3>Links</h3>
                        <div class="dashboard-card-rows links">
                            {links.map(link => <a href={link.url} target="_blank" key={link.id}>{link.title}</a>)}
                        </div>
                    </div>}
                    <div class="card dashboard v-spacing">
                        <h3>Latest published posts</h3>
                        <div class="dashboard-card-rows">
                            {posts && posts.length > 0 && <>
                                {posts.map(post => <a href={`/${settings.blog_url}/${post.slug}`} target="_blank">
                                    <img src={post.image} alt={post.title} style={{ visibility: post.image ? 'initial' : 'hidden' }}/>
                                    <div>
                                        <b>{post.title}</b>
                                        <span class="subtitle">{post.user_id ? `by ${post.user_name}` : <>&nbsp;</>}</span>
                                    </div>
                                </a>)}
                            </>}
                            {posts && posts.length == 0 && <span class="empty">No results</span>}
                        </div>
                    </div>
                </div>
                <div class="grid">
                    <div class="card dashboard v-spacing">
                        <h3>Start Creating</h3>
                        <div class="start-creating">
                            <a href="/console/pages/edit"><IconBook/> <span>Create Page</span></a>
                            <a href="/console/posts/edit"><IconPencil/> <span>Write Post</span></a>
                            <a href="/console/users/edit"><IconUser/> <span>Add User</span></a>
                            <a href="/console/tags/edit"><IconTag/> <span>Add Tag</span></a>
                        </div>
                    </div>
                    <div class="card dashboard v-spacing">
                        <h3>Statistics</h3>
                        <div>
                            <div class="input-group">
                                <b>Posts</b>
                                <span>{stats.total_posts} Published, {stats.total_scheduled_posts} Scheduled, {stats.total_draft_posts} Draft</span>
                            </div>
                            <div class="input-group">
                                <b>Pages</b>
                                <span>{stats.total_pages} Published, {stats.total_draft_pages} Draft</span>
                            </div>
                            <div class="input-group">
                                <b>Users</b>
                                <span>{stats.total_users} Active, {stats.total_inactive_users} Inactive</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>);
}