import React, { useEffect, useState } from 'react';
import { getContentUrl, ImageDialog, Input, LoadingPage, makeRequest, MenuButton, Switch, Textarea } from '../utils/utils';
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
                    <select name="theme">
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
                    <select name="language">
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
                    <select name="timezone">
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

export default function Settings() {
    const version = document.querySelector('meta[name="version"]')?.content;
    const location = useLocation();
    const [ hash, setHash ] = useState(location.hash);
    const { user, settings } = useOutletContext();
    const [ data, setData ] = useState(undefined);
    const [ loading, setLoading ] = useState(false);

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
                        <a href="#general" data-checked={hash == '#general'}><IconSettings/> General</a>
                        <a href="#meta" data-checked={hash == '#meta'}><IconNote/> Meta</a>
                        <a href="#data" data-checked={hash == '#data'}><IconDatabase/> Data</a>
                        <a href="#advanced" data-checked={hash == '#advanced'}><IconTerminal/> Advanced</a>
                        <a href="#info" data-checked={hash == '#info'}><IconServer/> Server Info</a>
                        <a href="#code" data-checked={hash == '#code'}><IconCode/> Code</a>
                        <a href="#update" data-checked={hash == '#update'}><IconSync/> Update</a>
                    </div>
                    <p class="version">Version: {version}</p>
                </div>
            </div>
            {settings && <>
                {hash == '#general' && <General data={data} setData={setData}/>}
                {hash == '#meta' && <Meta data={data} setData={setData}/>}
            </>}
        </div>
        <div id="image-dialog" class="dialog image-dialog">
            <div></div>
        </div>
    </form>);
}