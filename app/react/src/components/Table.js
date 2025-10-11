import React, { useEffect, useMemo, useState } from 'react';
import { MenuButton, useRequest } from '../utils/utils';
import { IconGlass } from '../utils/icons';

const DefaultHeader = ({ title, addLink = null }) => {
    return <>
        <div class="page-title">
            <MenuButton/>
            <div>
                <h2>{title}</h2>
                <span id="total-items">&nbsp;</span>
                <span id="selected-items"></span>
            </div>
        </div>
        {addLink && <a href={addLink} class="button" title="New"><b>+</b>&nbsp;New</a>}
    </>;
};

const getQueryString = (filters, search, page) => {
    let values = {};

    Object.keys(filters).map(key => {
        let val = filters[key].options.find(opt => opt.selected)?.key;
        if (val) {
            values[key] = val;
        }
    });

    if (search) {
        values.search = search;
    }

    if (page > 1) {
        values.page = page;
    }

    return (new URLSearchParams(values)).toString();
};

export const Table = ({
    url,
    title = '',
    CustomHeader = null,
    ExtraHeader = null,
    addLink = false,
    filters: initialFilters = [],
    columns = [],
    rowOnClick = null,
}) => {
    const params = useMemo(() => new URLSearchParams(window.location.search), []);
    const [ page, setPage ] = useState(params.get('page') ? parseInt(params.get('page')) : 1);
    const [ search, setSearch ] = useState(params.get('search') || '');
    const [ input_search, setInputSearch ] = useState(params.get('search') || '');
    const [ filters, setFilters ] = useState(initialFilters);
    const [ query_string, setQueryString ] = useState(getQueryString(filters, search, page));
    const [ rows, setRows ] = useState([]);
    const { data: page_req, is_loading, is_error, fetch } = useRequest({
        method: 'GET',
        url: url + (query_string ? `?${query_string}` : ''),
        data: {},
    });

    useEffect(() => {
        setQueryString(getQueryString(filters, search, page));
    }, [ filters, search, page ]);

    useEffect(() => {
        fetch();
    }, [ query_string ]);

    useEffect(() => {
        const page_rows = page_req?.data?.data || null;
        if (page_rows) {
            setRows(page == 1 ? page_rows : [ ...rows, ...page_rows ]);
        }
    }, [ page_req ]);

    const Filter = ({ id }) => {
        const filter = filters[id];

        return <div class="input-group">
            {filter.title && <label>{filter.title}</label>}
            <select onChange={e => {
                let aux = { ...filter };

                Object.keys(aux.options).map(opt_key => {
                    aux.options[opt_key].selected = aux.options[opt_key].key === e.target.value;
                });

                setFilters({ ...filters, [id]: aux });
                setPage(1);
            }}>
                {Object.keys(filter.options).map(opt_key => <option
                    value={filter.options[opt_key].key}
                    selected={filter.options[opt_key].selected}
                >{filter.options[opt_key].title}</option>)}
            </select>
        </div>;
    };

    if (is_loading) return <p>Cargando...</p>;
    if (is_error) return <p>Error al cargar los datos.</p>;

    return <>
        <div>
            {CustomHeader ? <CustomHeader/> : <DefaultHeader title={title} addLink={addLink}/>}
        </div>
        <form class="filters" onSubmit={e => {
            e.preventDefault();
            setPage(1);
            setSearch(input_search);
            
            const aux = getQueryString(filters, input_search, 1);
            if (aux !== query_string) {
                setQueryString(aux);
            } else {
                fetch();
            }
        }}>
            {Object.keys(filters).map(key => <Filter key={key} id={key}/>)}
            <input type="text" name="search" placeholder="Search" value={input_search} onChange={e => setInputSearch(e.target.value)}/>
            <button type="submit"><IconGlass/></button>
        </form>
        {ExtraHeader && <ExtraHeader/>}
        <div class="listing-container">
            <div class="listing">
                <div class="listing-row header">
                    {columns.filter(c => c.condition === undefined || c.condition).map(c => <div className={c.class} title={c.title}>{c.title}</div>)}
                </div>
            </div>
            <div class="listing">
                {rows.map((row, index) => (
                    <div key={row.id || index} class="listing-row" onClick={e => rowOnClick ? rowOnClick(row, e) : null}>
                        {columns.filter(c => c.condition === undefined || c.condition).map(c => <div className={c.class}>{c.content(row, index)}</div>)}
                    </div>
                ))}
            </div>
        </div>
        {page_req?.data?.meta?.next_page && <button id="load-more" class="light" onClick={() => setPage(page + 1)}>Load more</button>}
    </>;
};
