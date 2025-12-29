import React, { useEffect, useRef, useState } from 'react';
import { downloadFile, formatSize, getContentUrl, ImageDialog, Input, LoadingPage, makeRequest, MenuButton, Switch, Textarea } from '../utils/utils';
import { IconCode, IconDatabase, IconNote, IconServer, IconSettings, IconSync, IconTerminal } from '../utils/icons';
import { useLocation, useOutletContext } from 'react-router-dom';
import { useI18n } from '../providers/I18nProvider';

const General = ({ data, setData }) => {
    const [ open_image_dialog, setOpenImageDialog ] = useState(false);
    const { t } = useI18n();

    return <div class="grid">
        <div class="card v-spacing">
            <div class="input-group">
                <label>{t('logo')}</label>
                <img src={data.logo ? getContentUrl(data.logo) : '/public/assets/no-image.svg'} class={`logo pointer ${!data.logo ? 'empty-img' : ''}`} alt="logo" onClick={() => setOpenImageDialog(true)}/>
                {open_image_dialog && <ImageDialog onSave={path => setData({ ...data, logo: path })} onClose={() => setOpenImageDialog(false)}/>}
            </div>
            <div class="input-group">
                <label htmlFor="title">{t('title')}</label>
                <Input id="title" type="text" value={data.title} onChange={e => setData({ ...data, title: e.target.value })} charCount={true}/>
            </div>
            <div class="input-group-container">
                <div class="input-group">
                    <label htmlFor="blog-url">{t('blog_url')}</label>
                    <Input id="blog-url" type="text" placeholder="/blog" value={data.blog_url} onChange={e => setData({ ...data, blog_url: e.target.value })}/>
                </div>
                <div class="input-group">
                    <label htmlFor="rss">{t('rss_feed_url')}</label>
                    <Input id="rss" type="text" placeholder="/rss" value={data.rss} onChange={e => setData({ ...data, rss: e.target.value })}/>
                </div>
            </div>
            <div class="input-group-container">
                <div class="input-group">
                    <label>{t('theme')}</label>
                    <select onChange={e => setData({ ...data, theme: e.target.value })}>
                        {Object.keys(data.meta.themes).map(theme => <option value={theme} selected={data.theme == data.meta.themes[theme]}>{data.meta.themes[theme]}</option>)}
                    </select>
                </div>
                <div class="input-group">
                    <label htmlFor="per_page">{t('items_per_page')}</label>
                    <input id="per_page" name="per_page" type="number" placeholder="20" min="1" value={data.per_page} onChange={e => setData({ ...data, per_page: e.target.value })}/>
                </div>
            </div>
            <div class="input-group-container">
                <div class="input-group">
                    <label>{t('system_language')}</label>
                    <span class="description">{t('system_language')}</span>
                    <select onChange={e => setData({ ...data, language: e.target.value })}>
                        {data.meta.languages.map(lang => <option value={lang} selected={data.language == lang}>{lang}</option>)}
                    </select>
                </div>
                <div class="input-group">
                    <label htmlFor="date_format">{t('date_format')}</label>
                    <span class="description">Must follow a valid <a href="https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax" target="_blank">ICU date</a> format</span>
                    <input id="date_format" name="date_format" type="text" placeholder="MMM d, Y" value={data.date_format} onChange={e => setData({ ...data, date_format: e.target.value })}/>
                </div>
            </div>
            <div class="input-group-container">
                <div class="input-group">
                    <label>{t('timezone')}</label>
                    <select onChange={e => setData({ ...data, timezone: e.target.value })}>
                        {data.meta.timezones.map(tz => <option value={tz} selected={data.timezone == tz}>{tz.replace('_', ' ')}</option>)}
                    </select>
                </div>
                <div class="input-group">
                    <label>{t('maintenance_mode')}</label>
                    <Switch checked={data.maintenance == 1} onChange={e => setData({ ...data, maintenance: e.target.checked ? 1 : 0 })}/>
                </div>
            </div>
        </div>
    </div>;
};

const Meta = ({ data, setData }) => {
    const { t } = useI18n();

    return <div class="grid">
        <div class="card v-spacing">
            <div class="input-group">
                <label for="meta_title">{t('meta_title')}</label>
                <Input id="meta_title" type="text" value={data.meta_title} onChange={e => setData({ ...data, meta_title: e.target.value })} charCount/>
            </div>
            <div class="input-group">
                <label for="description">{t('description')}</label>
                <Textarea value={data.description} onChange={e => setData({ ...data, description: e.target.value })} charCount/>
            </div>
            <div class="input-group">
                <label for="meta_description">{t('meta_description')}</label>
                <Textarea value={data.meta_description} onChange={e => setData({ ...data, meta_description: e.target.value })} charCount/>
            </div>
            <div class="input-group">
                <label for="meta_keywords">{t('meta_keywords')}</label>
                <Input type="text" value={data.meta_keywords} onChange={e => setData({ ...data, meta_keywords: e.target.value })}/>
            </div>
        </div>
    </div>;
};

const Data = ({ data, setData, user }) => {
    const file_ref = useRef(null);
    const [ database_file, setDatabaseFile ] = useState(null);
    const { t } = useI18n();

    const downloadDatabase = () => {
        makeRequest({
            method: 'GET',
            url: '/api/db',
            options: { responseType: 'blob' },
        }).then(res => downloadFile(res.data, 'data.json'));
    };

    const uploadDatabase = async () => {
        if (confirm(t('confirm_update_database'))) {
            let form_data = new FormData();
            form_data.append('file', database_file);
            makeRequest({
                method: 'POST',
                url: '/api/db',
                data: form_data,
            }).finally(() => {
                file_ref.current.value = '';
            });
        }
    };

    const resetViewsCount = () => {
        if (confirm(t('confirm_reset_views'))) {
            makeRequest({
                method: 'GET',
                url: '/api/reset_views_count',
            }).then(res => alert(res?.data?.success ? t('views_reset_successfully') : t('error_resetting_views')));
        }
    };

    return <div class="grid">
        <div class="card v-spacing">
            <div class="input-group">
                <label>{t('download_database')}</label>
                <button type="button" class="light" onClick={downloadDatabase} disabled={!user?.actions?.edit_settings}>.json</button>
            </div>
            <div id="db-upload" class="input-group">
                <label for="database">{t('upload_database')}</label>
                <div class="input-file">
                    <input ref={file_ref} id="database" type="file" name="db" class="hidden" onChange={e => setDatabaseFile(e.target.files[0])}/>
                    <input type="text" disabled value={database_file?.name}/>
                    <label htmlFor="database" class="pointer">{t('select_file')}</label>
                </div>
                <button type="button" class="light" onClick={uploadDatabase} disabled={!user?.actions?.edit_settings}>Upload .json</button>
            </div>
            <div class="input-group">
                <label>{t('views_counter')}</label>
                <Switch checked={data.views_count == 1} onChange={e => setData({ ...data, views_count: e.target.checked ? 1 : 0 })}/>
                <div id="reset-views">
                    <button type="button" class="light" onClick={resetViewsCount} disabled={!user?.actions?.edit_settings}>{t('reset_views_count')}</button>
                </div>
            </div>
        </div>
    </div>;
};

const Advanced = ({ data, setData, user }) => {
    const [ logs, setLogs ] = useState(undefined);
    const { t } = useI18n();

    useEffect(() => {
        loadLogs();
    }, []);

    const loadLogs = () => {
        makeRequest({
            method: 'GET',
            url: '/api/logs',
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
            url: '/api/logs',
        }).then(res => {
            alert(res?.data?.success ? t('logs_deleted_successfully') : t('error_deleting_logs'));
            setLogs(undefined);
        }).finally(() => loadLogs());
    };

    return <div class="grid">
        <div class="card v-spacing">
            <div class="input-group">
                <label for="session_lifetime">{t('session_lifetime')}</label>
                <span class="description">PHP Session lifetime in seconds (e.g. 3600 = 1 hour)</span>
                <input id="session_lifetime" type="number" value={data.session_lifetime} onChange={e => setData({ ...data, session_lifetime: e.target.value })}/>
            </div>
            <div class="input-group">
                <label for="samesite_cookie">{t('session_samesite_cookie')}</label>
                <span class="description">PHP session SameSite cookie</span>
                <select onChange={e => setData({ ...data, samesite_cookie: e.target.value })}>
                    {[ 'None', 'Lax', 'Strict' ].map(cookie => <option value={cookie} selected={data.samesite_cookie == cookie}>{cookie}</option>)}
                </select>
            </div>
            <div class="input-group">
                <label>{t('display_errors')}</label>
                <Switch checked={data.display_errors == 1} onChange={e => setData({ ...data, display_errors: e.target.checked ? 1 : 0 })}/>
            </div>
            <div class="input-group">
                <label>{t('log_errors')}</label>
                <Switch checked={data.log_errors == 1} onChange={e => setData({ ...data, log_errors: e.target.checked ? 1 : 0 })}/>
            </div>
            <div class="input-group">
                <label for="log_file">{t('log_file')}</label>
                <span class="description">Relative to the Aurora root folder</span>
                <input id="log_file" name="log_file" type="text" value={data.log_file} onChange={e => setData({ ...data, log_file: e.target.value })}/>
            </div>
        </div>
        {data.log_file && <div class="card v-spacing">
            <div id="logs" class="input-group">
                <label>{t('logs')}</label>
                <textarea placeholder={logs === undefined ? t('loading') : t('no_logs')} readonly value={logs}></textarea>
                <div class="input-group">
                    <button type="button" class="light" onClick={downloadLogs}>{t('download')}</button>
                    <button type="button" class="delete" onClick={deleteLogs} disabled={!user?.actions?.edit_settings}>{t('clear')}</button>
                </div>
            </div>
        </div>}
    </div>;
};

const Info = () => {
    const [ server, setServer ] = useState(undefined);
    const { t } = useI18n();

    useEffect(() => {
        makeRequest({
            method: 'GET',
            url: '/api/server',
        }).then(res => setServer(res?.data));
    }, []);

    if (!server) {
        return null;
    }

    return <div class="grid">
        <div class="card v-spacing">
            <div class="input-group">
                <label>{t('operating_system')}</label>
                <span>{server.os}</span>
            </div>
            <div class="input-group">
                <label>{t('php_version')}</label>
                <span>{server.php_version}</span>
            </div>
            <div class="input-group">
                <label>{t('database')}</label>
                <span>{server.db_dsn}</span>
            </div>
            <div class="input-group">
                <label>{t('host_name')}</label>
                <span>{server.host_name}</span>
            </div>
            <div class="input-group">
                <label>{t('root_folder')}</label>
                <span>{server.root_folder}</span>
            </div>
            <div class="input-group">
                <label>{t('time')}</label>
                <span>{server.date}</span>
            </div>
            <div class="input-group">
                <label>{t('memory_limit')}</label>
                <span>{formatSize(server.memory_limit)}</span>
            </div>
            <div class="input-group">
                <label>{t('file_size_upload_limit')}</label>
                <span class="description">The value is the lowest possible value between the <code>post_max_size</code> and the <code>upload_max_filesize</code> options of your PHP configuration.</span>
                <span>{formatSize(server.file_size_limit)}</span>
            </div>
        </div>
    </div>;
};

const Code = ({ data, setData }) => {
    const { t } = useI18n();

    return <div class="grid">
        <div class="card v-spacing">
            <div class="input-group">
                <label for="site-header">{t('site_header')}</label>
                <span class="description">Code here will be injected into the header of all pages.</span>
                <textarea id="site-header" name="header_code" class="code" value={data.header_code} onChange={e => setData({ ...data, header_code: e.target.value })}></textarea>
            </div>
            <div class="input-group">
                <label for="site-footer">{t('site_footer')}</label>
                <span class="description">Code here will be injected into the footer of all pages.</span>
                <textarea id="site-footer" name="footer_code" class="code" value={data.footer_code} onChange={e => setData({ ...data, footer_code: e.target.value })}></textarea>
            </div>
            <div class="input-group">
                <label for="post-code">{t('post_code')}</label>
                <span class="description">Code here will be injected at the bottom of all post pages. Useful for things like adding a comment system.</span>
                <textarea id="post-code" name="post_code" class="code" value={data.post_code} onChange={e => setData({ ...data, post_code: e.target.value })}></textarea>
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
    const { t } = useI18n();
    const SECTIONS = [
        { id: 'general', name: t('general'), icon: IconSettings, section: General },
        { id: 'meta', name: t('meta'), icon: IconNote, section: Meta },
        { id: 'data', name: t('data'), icon: IconDatabase, section: Data },
        { id: 'advanced', name: t('advanced'), icon: IconTerminal, section: Advanced },
        { id: 'info', name: t('server_info'), icon: IconServer, section: Info },
        { id: 'code', name: t('code'), icon: IconCode, section: Code },
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

    useEffect(() => {
        if (SECTIONS.find(sec => '#' + sec.id == hash) === undefined) {
            setHash('#' + SECTIONS[0].id);
        }
    }, [ hash ]);

    const save = e => {
        e.preventDefault();
        setLoading(true);
        let new_data = { ...data };
        delete new_data.meta;
        makeRequest({
            method: 'POST',
            url: '/api/settings',
            data: new_data,
        }).then(res => alert(res?.data?.success ? t('settings_saved_successfully') : t('error_saving_settings')))
        .finally(() => setLoading(false));
    };

    if (!data) {
        return <LoadingPage/>;
    }

    return (<form id="settings-form" class="content" onSubmit={save}>
        <div>
            <div class="page-title">
                <MenuButton/>
                <h2>{t('settings')}</h2>
            </div>
            <div class="buttons">
                <button type="submit" disabled={!user?.actions?.edit_settings || loading}>{t('save')}</button>
            </div>
        </div>
        <div class="grid grid-two-columns wide">
            <div class="grid">
                <div>
                    <div class="tabs">
                        {SECTIONS.map(section => <a href={'#' + section.id} data-checked={hash == ('#' + section.id)}><section.icon/> {section.name}</a>)}
                    </div>
                    <p class="version">{t('version')}: {version}</p>
                </div>
            </div>
            {settings && SECTIONS.map(section => (hash == ('#' + section.id) && <section.section data={data} setData={setData} user={user}/>))}
        </div>
    </form>);
}