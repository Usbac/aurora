import React, { useEffect, useRef, useState } from 'react';
import { downloadFile, formatSize, getContentUrl, ImageDialog, Input, LoadingPage, makeRequest, MenuButton, Switch, Textarea } from '../utils/utils';
import { IconCode, IconDatabase, IconNote, IconServer, IconSettings, IconSync, IconTerminal } from '../utils/icons';
import { useLocation, useOutletContext } from 'react-router-dom';

const General = ({ data, setData }) => {
    const [ open_image_dialog, setOpenImageDialog ] = useState(false);

    return <div class="grid">
        <div class="card v-spacing">
            <div class="input-group">
                <label>Logo</label>
                <img src={data.logo ? getContentUrl(data.logo) : '/public/assets/no-image.svg'} class={`logo pointer ${!data.logo ? 'empty-img' : ''}`} alt="logo" onClick={() => setOpenImageDialog(true)}/>
                {open_image_dialog && <ImageDialog onSave={path => setData({ ...data, logo: path })} onClose={() => setOpenImageDialog(false)}/>}
            </div>
            <div class="input-group">
                <label htmlFor="title">Title</label>
                <Input id="title" type="text" value={data.title} onChange={e => setData({ ...data, title: e.target.value })} charCount={true}/>
            </div>
            <div class="input-group-container">
                <div class="input-group">
                    <label htmlFor="blog-url">Blog URL</label>
                    <Input id="blog-url" type="text" placeholder="/blog" value={data.blog_url} onChange={e => setData({ ...data, blog_url: e.target.value })}/>
                </div>
                <div class="input-group">
                    <label htmlFor="rss">RSS feed URL</label>
                    <Input id="rss" type="text" placeholder="/rss" value={data.rss} onChange={e => setData({ ...data, rss: e.target.value })}/>
                </div>
            </div>
            <div class="input-group-container">
                <div class="input-group">
                    <label>Theme</label>
                    <select onChange={e => setData({ ...data, theme: e.target.value })}>
                        {Object.keys(data.meta.themes).map(theme => <option value={theme} selected={data.theme == data.meta.themes[theme]}>{data.meta.themes[theme]}</option>)}
                    </select>
                </div>
                <div class="input-group">
                    <label htmlFor="per_page">Items per page</label>
                    <input id="per_page" name="per_page" type="number" placeholder="20" min="1" value={data.per_page} onChange={e => setData({ ...data, per_page: e.target.value })}/>
                </div>
            </div>
            <div class="input-group-container">
                <div class="input-group">
                    <label>System language</label>
                    <span class="description">System language</span>
                    <select onChange={e => setData({ ...data, language: e.target.value })}>
                        {data.meta.languages.map(lang => <option value={lang} selected={data.language == lang}>{lang}</option>)}
                    </select>
                </div>
                <div class="input-group">
                    <label htmlFor="date_format">Date format</label>
                    <span class="description">Must follow a valid <a href="https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax" target="_blank">ICU date</a> format</span>
                    <input id="date_format" name="date_format" type="text" placeholder="MMM d, Y" value={data.date_format} onChange={e => setData({ ...data, date_format: e.target.value })}/>
                </div>
            </div>
            <div class="input-group-container">
                <div class="input-group">
                    <label>Timezone</label>
                    <select onChange={e => setData({ ...data, timezone: e.target.value })}>
                        {data.meta.timezones.map(tz => <option value={tz} selected={data.timezone == tz}>{tz.replace('_', ' ')}</option>)}
                    </select>
                </div>
                <div class="input-group">
                    <label>Maintenance mode</label>
                    <Switch checked={data.maintenance == 1} onChange={e => setData({ ...data, maintenance: e.target.checked })}/>
                </div>
            </div>
        </div>
    </div>;
};

const Meta = ({ data, setData }) => {
    return <div class="grid">
        <div class="card v-spacing">
            <div class="input-group">
                <label for="meta_title">Meta title</label>
                <Input id="meta_title" type="text" value={data.meta_title} onChange={e => setData({ ...data, meta_title: e.target.value })} charCount/>
            </div>
            <div class="input-group">
                <label for="description">Description</label>
                <Textarea value={data.description} onChange={e => setData({ ...data, description: e.target.value })} charCount/>
            </div>
            <div class="input-group">
                <label for="meta_description">Meta description</label>
                <Textarea value={data.meta_description} onChange={e => setData({ ...data, meta_description: e.target.value })} charCount/>
            </div>
            <div class="input-group">
                <label for="meta_keywords">Meta keywords</label>
                <Input type="text" value={data.meta_keywords} onChange={e => setData({ ...data, meta_keywords: e.target.value })}/>
            </div>
        </div>
    </div>;
};

const Data = ({ data, setData, user }) => {
    const file_ref = useRef(null);
    const [ database_file, setDatabaseFile ] = useState(null);

    const downloadDatabase = () => {
        makeRequest({
            method: 'GET',
            url: '/api/v2/db',
            options: { responseType: 'blob' },
        }).then(res => downloadFile(res.data, 'data.json'));
    };

    const uploadDatabase = async () => {
        if (confirm('Are you sure about updating the current database?')) {
            makeRequest({
                method: 'POST',
                url: '/api/v2/db',
                data: { file: database_file },
            }).finally(() => {
                file_ref.current.value = '';
            });
        }
    };

    const resetViewsCount = () => {
        if (confirm('Are you sure about resetting the views count of all items?')) {
            makeRequest({
                method: 'GET',
                url: '/api/v2/reset_views_count',
            }).then(res => alert(res?.data?.success ? 'Done' : 'Error'));
        }
    };

    return <div class="grid">
        <div class="card v-spacing">
            <div class="input-group">
                <label>Download database</label>
                <button type="button" class="light" onClick={downloadDatabase} disabled={!user?.actions?.edit_settings}>.json</button>
            </div>
            <div id="db-upload" class="input-group">
                <label for="database">Upload database</label>
                <div class="input-file">
                    <input ref={file_ref} id="database" type="file" name="db" class="hidden" onChange={e => setDatabaseFile(e.target.files[0])}/>
                    <input type="text" disabled value={database_file?.name}/>
                    <label htmlFor="database" class="pointer">Select file</label>
                </div>
                <button type="button" class="light" onClick={uploadDatabase} disabled={!user?.actions?.edit_settings}>Upload .json</button>
            </div>
            <div class="input-group">
                <label>Views counter</label>
                <Switch checked={data.views_count == 1} onChange={e => setData({ ...data, views_count: e.target.checked })}/>
                <div id="reset-views">
                    <button type="button" class="light" onClick={resetViewsCount} disabled={!user?.actions?.edit_settings}>Reset views count</button>
                </div>
            </div>
        </div>
    </div>;
};

const Advanced = ({ data, setData, user }) => {
    const [ logs, setLogs ] = useState(undefined);

    useEffect(() => {
        loadLogs();
    }, []);

    const loadLogs = () => {
        makeRequest({
            method: 'GET',
            url: '/api/v2/logs',
        }).then(res => {
            setLogs(res?.data || '');
        });
    };

    const downloadLogs = () => {
        downloadFile(logs, `Aurora ${new Date().toISOString().slice(0, 19).replace('T', ' ')}.log`);
    };

    const deleteLogs = () => {
        makeRequest({
            method: 'DELETE',
            url: '/api/v2/logs',
        }).then(res => {
            alert(res?.data?.success ? 'Done' : 'Error');
            setLogs(undefined);
        }).finally(() => loadLogs());
    };

    return <div class="grid">
        <div class="card v-spacing">
            <div class="input-group">
                <label for="session_lifetime">Session lifetime</label>
                <span class="description">PHP Session lifetime in seconds (e.g. 3600 = 1 hour)</span>
                <input id="session_lifetime" type="number" value={data.session_lifetime} onChange={e => setData({ ...data, session_lifetime: e.target.value })}/>
            </div>
            <div class="input-group">
                <label for="samesite_cookie">Session SameSite cookie</label>
                <span class="description">PHP session SameSite cookie</span>
                <select onChange={e => setData({ ...data, samesite_cookie: e.target.value })}>
                    {[ 'None', 'Lax', 'Strict' ].map(cookie => <option value={cookie} selected={data.samesite_cookie == cookie}>{cookie}</option>)}
                </select>
            </div>
            <div class="input-group">
                <label>Display errors</label>
                <Switch checked={data.display_errors == 1} onChange={e => setData({ ...data, display_errors: e.target.checked })}/>
            </div>
            <div class="input-group">
                <label>Log errors</label>
                <Switch checked={data.log_errors == 1} onChange={e => setData({ ...data, log_errors: e.target.checked })}/>
            </div>
            <div class="input-group">
                <label for="log_file">Log file</label>
                <span class="description">Relative to the Aurora root folder</span>
                <input id="log_file" name="log_file" type="text" value={data.log_file} onChange={e => setData({ ...data, log_file: e.target.value })}/>
            </div>
        </div>
        {data.log_file && <div class="card v-spacing">
            <div id="logs" class="input-group">
                <label>Logs</label>
                <textarea placeholder={logs === undefined ? 'Loading...' : 'No logs'} readonly value={logs}></textarea>
                <div class="input-group">
                    <button type="button" class="light" onClick={downloadLogs}>Download</button>
                    <button type="button" class="delete" onClick={deleteLogs} disabled={!user?.actions?.edit_settings}>Clear</button>
                </div>
            </div>
        </div>}
    </div>;
};

const Info = () => {
    const [ server, setServer ] = useState(undefined);

    useEffect(() => {
        makeRequest({
            method: 'GET',
            url: '/api/v2/server',
        }).then(res => setServer(res?.data));
    }, []);

    if (!server) {
        return null;
    }

    return <div class="grid">
        <div class="card v-spacing">
            <div class="input-group">
                <label>Operating system</label>
                <span>{server.os}</span>
            </div>
            <div class="input-group">
                <label>PHP version</label>
                <span>{server.php_version}</span>
            </div>
            <div class="input-group">
                <label>Database</label>
                <span>{server.db_dsn}</span>
            </div>
            <div class="input-group">
                <label>Host name</label>
                <span>{server.host_name}</span>
            </div>
            <div class="input-group">
                <label>Root folder</label>
                <span>{server.root_folder}</span>
            </div>
            <div class="input-group">
                <label>Time</label>
                <span>{server.date}</span>
            </div>
            <div class="input-group">
                <label>Memory limit</label>
                <span>{formatSize(server.memory_limit)}</span>
            </div>
            <div class="input-group">
                <label>File size upload limit</label>
                <span class="description">The value is the lowest possible value between the <code>post_max_size</code> and the <code>upload_max_filesize</code> options of your PHP configuration.</span>
                <span>{formatSize(server.file_size_limit)}</span>
            </div>
        </div>
    </div>;
};

const Code = ({ data, setData }) => {
    return <div class="grid">
        <div class="card v-spacing">
            <div class="input-group">
                <label for="site-header">Site header</label>
                <span class="description">Code here will be injected into the header of all pages.</span>
                <textarea id="site-header" name="header_code" class="code" value={data.header_code} onChange={e => setData({ ...data, header_code: e.target.value })}></textarea>
            </div>
            <div class="input-group">
                <label for="site-footer">Site footer</label>
                <span class="description">Code here will be injected into the footer of all pages.</span>
                <textarea id="site-footer" name="footer_code" class="code" value={data.footer_code} onChange={e => setData({ ...data, footer_code: e.target.value })}></textarea>
            </div>
            <div class="input-group">
                <label for="post-code">Post code</label>
                <span class="description">Code here will be injected at the bottom of all post pages. Useful for things like adding a comment system.</span>
                <textarea id="post-code" name="post_code" class="code" value={data.post_code} onChange={e => setData({ ...data, post_code: e.target.value })}></textarea>
            </div>
            <div class="input-group">
                <label for="editor-code">Editor code</label>
                <span class="description">Code here will be injected into the editor of all pages.</span>
                <textarea id="editor-code" name="editor_code" class="code" value={data.editor_code} onChange={e => setData({ ...data, editor_code: e.target.value })}></textarea>
            </div>
        </div>
    </div>;
};

export default function Settings() {
    const version = document.querySelector('meta[name="version"]')?.content;
    const location = useLocation();
    const [ hash, setHash ] = useState(location.hash);
    const { user, settings } = useOutletContext();
    const [ data, setData ] = useState(undefined);
    const [ loading, setLoading ] = useState(false);
    const SECTIONS = [
        { id: 'general', name: 'General', icon: IconSettings, section: General },
        { id: 'meta', name: 'Meta', icon: IconNote, section: Meta },
        { id: 'data', name: 'Data', icon: IconDatabase, section: Data },
        { id: 'advanced', name: 'Advanced', icon: IconTerminal, section: Advanced },
        { id: 'info', name: 'Server Info', icon: IconServer, section: Info },
        { id: 'code', name: 'Code', icon: IconCode, section: Code },
        //{ id: 'update', name: 'Update', icon: IconSync, section: <></> },
    ];

    useEffect(() => {
        const onHashChange = () => setHash(window.location.hash);
        window.addEventListener('hashchange', onHashChange);
        return () => window.removeEventListener('hashchange', onHashChange);
    }, []);

    useEffect(() => {
        setData(settings);
    }, [ settings ]);

    const save = e => {
        e.preventDefault();
        setLoading(true);
        makeRequest({
            method: 'POST',
            url: '/api/v2/settings',
            data: data,
        }).then(res => alert(res?.data?.success ? 'Done' : 'Error'))
        .finally(() => setLoading(false));
    };

    if (!data) {
        return <LoadingPage/>;
    }

    return (<form id="settings-form" class="content" onSubmit={save}>
        <div>
            <div class="page-title">
                <MenuButton/>
                <h2>Settings</h2>
            </div>
            <div class="buttons">
                <button type="submit" disabled={!user?.actions?.edit_settings || loading}>Save</button>
            </div>
        </div>
        <div class="grid grid-two-columns wide">
            <div class="grid">
                <div>
                    <div class="tabs">
                        {SECTIONS.map(section => <a href={'#' + section.id} data-checked={hash == ('#' + section.id)}><section.icon/> {section.name}</a>)}
                    </div>
                    <p class="version">Version: {version}</p>
                </div>
            </div>
            {settings && SECTIONS.map(section => (hash == ('#' + section.id) && <section.section data={data} setData={setData} user={user}/>))}
        </div>
        <div id="image-dialog" class="dialog image-dialog">
            <div></div>
        </div>
    </form>);
}