import React, { createContext, useContext, useState, useEffect, useMemo } from 'react';

const I18nContext = createContext();

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
        const translation = translations[language]?.[key];

        if (!translation) {
            throw new Error(`Unknown translation key: "${key}" for language "${language}"`);
        }

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

export const useI18n = () => {
    const context = useContext(I18nContext);

    if (!context) {
        throw new Error('useI18n must be used within an I18nProvider');
    }

    return context;
};
