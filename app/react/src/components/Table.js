import React, { useEffect, useMemo, useState } from 'react';
import { MenuButton, useRequest } from '../utils/utils';
import { IconGlass } from '../utils/icons';

const Header = ({ title, totalItems, selectedItems = 0, options = [] }) => {
    return <div>
        <div class="page-title">
            <MenuButton/>
            <div>
                <h2>{title}</h2>
                <span id="total-items">{totalItems} item{totalItems != 1 ? 's' : ''}</span>
                {selectedItems > 0 && <span id="selected-items">{selectedItems} selected</span>}
            </div>
        </div>
        {options.map((opt, i) => <button key={i} className="button" onClick={opt.onClick}>{opt.content}</button>)}
    </div>;
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
    topOptions = [],
    filters: initialFilters = [],
    columns = [],
    rowOnClick = null,
    options: initialOptions = [],
}) => {
    const params = useMemo(() => new URLSearchParams(window.location.search), []);
    const [ page, setPage ] = useState(params.get('page') ? parseInt(params.get('page')) : 1);
    const [ select_mode, setSelectMode ] = useState(false);
    const [ selected_rows, setSelectedRows ] = useState([]);
    const [ search, setSearch ] = useState(params.get('search') || '');
    const [ input_search, setInputSearch ] = useState(params.get('search') || '');
    const [ filters, setFilters ] = useState(initialFilters);
    const [ query_string, setQueryString ] = useState(getQueryString(filters, search, page));
    const [ rows, setRows ] = useState([]);
    const options = initialOptions.filter(opt => opt.condition === undefined || opt.condition);
    const { data: page_req, is_loading, is_error, fetch } = useRequest({
        method: 'GET',
        url: url + (query_string ? `?${query_string}` : ''),
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

        if (page == 1) {
            setSelectedRows([]);
        }
    }, [ page_req ]);

    useEffect(() => {
        setSelectedRows([]);
    }, [ select_mode ]);

    const submit = e => {
        e.preventDefault();
        setPage(1);
        setSearch(input_search);
        
        const aux = getQueryString(filters, input_search, 1);
        if (aux !== query_string) {
            setQueryString(aux);
        } else {
            setSelectedRows([]);
            fetch();
        }
    };

    const toggleRow = i => {
        let aux = [ ...selected_rows ];

        if (selected_rows.includes(i)) {
            aux.splice(aux.indexOf(i), 1);
        } else {
            aux.push(i);
        }

        setSelectedRows(aux);
    };

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

    const Rows = () => {
        if (is_loading) return <p>Cargando...</p>;
        if (is_error) return <p>Error al cargar los datos.</p>;

        return rows.map((row, i) => <div
            key={i}
            class="listing-row"
            onClick={e => select_mode ? toggleRow(i) : (rowOnClick ? rowOnClick(row, e) : null)}
            data-selected={selected_rows.includes(i)}
        >
            {columns.filter(c => c.condition === undefined || c.condition).map(c => <div className={c.class}>{c.content(row, i)}</div>)}
        </div>);
    };

    return <>
        <Header
            title={title}
            totalItems={page_req?.data?.meta?.total_items}
            selectedItems={selected_rows.length}
            options={topOptions}
        />
        <form class="filters" onSubmit={submit}>
            {Object.keys(filters).map(key => <Filter key={key} id={key}/>)}
            <input type="text" name="search" placeholder="Search" value={input_search} onChange={e => setInputSearch(e.target.value)}/>
            <button type="submit"><IconGlass/></button>
        </form>
        {options.length > 0 && <div class="batch-options-container">
            {select_mode && <div>
                {options.map((opt, i) => <button
                    key={i}
                    className={opt.class}
                    onClick={() => opt.onClick(rows.filter((_, row_i) => selected_rows.includes(row_i)))}
                    disabled={selected_rows.length == 0}
                >{opt.title}</button>)}
            </div>}
            <button onClick={() => setSelectMode(!select_mode)}>{select_mode ? 'Done' : 'Select'}</button>
        </div>}
        <div class="listing-container">
            <div class="listing">
                <div class="listing-row header">
                    {columns.filter(c => c.condition === undefined || c.condition).map(c => <div className={c.class} title={c.title ?? undefined}>{c.title ?? ''}</div>)}
                </div>
            </div>
            <div class="listing">
                <Rows/>
            </div>
        </div>
        {page_req?.data?.meta?.next_page && <button id="load-more" class="light" onClick={() => setPage(page + 1)}>Load more</button>}
    </>;
};
