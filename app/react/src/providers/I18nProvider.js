import React, { createContext, useContext, useState, useEffect, useMemo } from 'react';

/** @type {React.Context<null | { language: string, t: function (string, ...*): string, changeLanguage: function (string): void, getLanguages: function (): string[] }>} */
const I18nContext = createContext();

/**
 * Loads every locale module from `../lang/*.js` (via `require.context`), provides translation helpers, and persists the active language in `localStorage` under `lang`.
 * @param {Object} props
 * @param {React.ReactNode} props.children
 * @param {string} [props.defaultLanguage='en'] - Initial language when `localStorage` has no `lang` value.
 * @returns {React.ReactElement}
 */
export const I18nProvider = ({ children, defaultLanguage = 'en' }) => {
    const [ language, setLanguage ] = useState(localStorage.getItem('lang') || defaultLanguage);
    const translations = useMemo(() => {
        const translation_context = require.context('../lang', false, /\.js$/);
        const res = {};
        
        translation_context.keys().forEach((file_name) => {
            res[file_name.replace('./', '').replace('.js', '')] = translation_context(file_name).default;
        });
        
        return res;
    }, []);

    useEffect(() => {
        localStorage.setItem('lang', language);
    }, [ language ]);

    const t = (key, ...params) => {
        const translation = translations[language]?.[key]
            ?? translations['en'][key]
            ?? key;

        let i = 0;
        return translation.replace(/%s|%d|%f/g, e => params[i++] ?? e);
    };

    const changeLanguage = (lang) => {
        if (translations[lang]) {
            setLanguage(lang);
        }
    };

    const getLanguages = () => {
        return Object.keys(translations);
    };

    return <I18nContext.Provider value={{
        language: language,
        t: t,
        changeLanguage: changeLanguage,
        getLanguages: getLanguages,
    }}>
        {children}
    </I18nContext.Provider>;
};

/**
 * Returns the i18n context from {@link I18nProvider}: current `language`, `t` for translated strings, `changeLanguage`, and `getLanguages`.
 * @returns {{
 *   language: string,
 *   t: function (string, ...*): string,
 *   changeLanguage: function (string): void,
 *   getLanguages: function (): string[]
 * }}
 *   `t` looks up the key in the active locale and replaces `%s`, `%d`, and `%f` placeholders in order with the extra arguments.
 *   Falls back to English, then to the key itself when a translation is missing.
 * @throws {Error} When used outside an {@link I18nProvider}.
 */
export const useI18n = () => {
    const context = useContext(I18nContext);

    if (!context) {
        throw new Error('useI18n must be used within an I18nProvider');
    }

    return context;
};
