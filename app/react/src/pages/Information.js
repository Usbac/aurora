import React from 'react';

export default function Information({ title, subtitle }) {
    return (<div className="content information-page">
        <h1>{title}</h1>
        {subtitle && <h2>{subtitle}</h2>}
    </div>);
}