import React, { useEffect, useState } from 'react';
import { LoadingPage, MenuButton } from '../utils/utils';
import { IconCode, IconDatabase, IconNote, IconServer, IconSettings, IconSync, IconTerminal } from '../utils/icons';
import { useLocation, useOutletContext } from 'react-router-dom';

export default function Settings() {
    const version = document.querySelector('meta[name="version"]')?.content;
    const location = useLocation();
    const [ hash, setHash ] = useState(location.hash);
    const { user, settings } = useOutletContext();

    useEffect(() => {
        const onHashChange = () => setHash(window.location.hash);
        window.addEventListener('hashchange', onHashChange);
        return () => window.removeEventListener('hashchange', onHashChange);
    }, []);

    if (!settings) {
        return <LoadingPage/>;
    }

    return (<form id="settings-form" class="content">
        <div>
            <div class="page-title">
                <MenuButton/>
                <h2>Settings</h2>
            </div>
            <div class="buttons">
                <button type="submit" disabled={!user?.actions?.edit_settings}>Save</button>
            </div>
        </div>
        <div class="grid grid-two-columns wide">
            <div class="grid">
                <div>
                    <div class="tabs">
                        <a href="#general"><IconSettings/> General</a>
                        <a href="#meta"><IconNote/> Meta</a>
                        <a href="#data"><IconDatabase/> Data</a>
                        <a href="#advanced"><IconTerminal/> Advanced</a>
                        <a href="#info"><IconServer/> Server Info</a>
                        <a href="#code"><IconCode/> Code</a>
                        <a href="#update"><IconSync/> Update</a>
                    </div>
                    <p class="version">Version: {version}</p>
                </div>
            </div>
        </div>
        <div id="image-dialog" class="dialog image-dialog">
            <div></div>
        </div>
    </form>);
}