import React, { useEffect, useImperativeHandle, useMemo, useRef, useState, forwardRef } from 'react';
import { MenuButton, useRequest } from './utils';
import { IconGlass, IconSpinner } from './icons';
import { useI18n } from '../providers/I18nProvider';

/**
 * Page title row with menu, total item count, optional selection count, and top action buttons.
 * @param {Object} props
 * @param {string} props.title
 * @param {number} props.totalItems
 * @param {number} [props.selectedItems=0]
 * @param {Array<{ condition?: boolean, onClick: function (): void, content: React.ReactNode }>} [props.options=[]]
 * @returns {React.ReactElement}
 */
const Header = ({ title, totalItems, selectedItems = 0, options = [] }) => {
    return <div>
        <div className="page-title">
            <MenuButton/>
            <div>
                <h2>{title}</h2>
                <span id="total-items">{totalItems} item{totalItems != 1 ? 's' : ''}</span>
                {selectedItems > 0 && <span id="selected-items">{selectedItems} selected</span>}
            </div>
        </div>
        <div className="page-options">
            {options.filter(opt => opt.condition === undefined || opt.condition).map((opt, i) => <button key={i} className="button" onClick={opt.onClick}>{opt.content}</button>)}
        </div>
    </div>;
};

/**
 * Builds a URL query string from the first selected option per filter, optional search text, and page when greater than 1.
 * @param {Object<string, { options: Array<{ key: *, selected?: boolean }> }>} filters - Filter state keyed by id.
 * @param {string} search - Search term or empty string.
 * @param {number} page - 1-based page; omitted from the string when 1.
 * @returns {string} URL-encoded query string without a leading `?`.
 */
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

/**
 * Admin listing table: GETs `url` with filters, search, and pagination query params.
 * Initializes `page` and `search` from the current URL; supports infinite scroll, row selection with batch actions, and an optional row click handler.
 * The forwarded `ref` exposes `{ refetch() }`, which resets to page 1 and reloads (via `useImperativeHandle`).
 * @param {Object} props
 * @param {string} props.url - List API endpoint (GET); the built query string is appended with `?` or `&` as needed.
 * @param {string} [props.title=''] - Shown in the header next to the menu.
 * @param {Array<{ condition?: boolean, onClick: function (): void, content: React.ReactNode }>} [props.topOptions=[]] - Buttons in the page title row.
 * @param {Object<string, { title?: string, options: Array<{ key: *, title: string, selected?: boolean }> }>} [props.filters={}] - Filter dropdowns; the first option starts selected per filter.
 * @param {Array<{ class: string, title?: string, condition?: boolean, content: function (Object, number): React.ReactNode }>} [props.columns=[]] - Column definitions; `content(row, rowIndex)` renders each cell.
 * @param {function (Object, React.SyntheticEvent): void | null} [props.rowOnClick=null] - Invoked on row click when not in selection mode.
 * @param {Array<{ title: React.ReactNode, class?: string, condition?: boolean, onClick: function (Object[]): void }>} [props.options=[]] - Batch actions when selection mode is on; `onClick` receives the selected row objects.
 * @returns {React.ReactElement}
 */
export const Table = forwardRef(({
    url,
    title = '',
    topOptions = [],
    filters: initialFilters = {},
    columns = [],
    rowOnClick = null,
    options: initialOptions = [],
}, ref) => {
    const params = useMemo(() => new URLSearchParams(window.location.search), []);
    const [ page, setPage ] = useState(params.get('page') ? parseInt(params.get('page')) : 1);
    const [ select_mode, setSelectMode ] = useState(false);
    const [ selected_rows, setSelectedRows ] = useState([]);
    const [ search, setSearch ] = useState(params.get('search') || '');
    const [ input_search, setInputSearch ] = useState(params.get('search') || '');
    const [ filters, setFilters ] = useState({});
    const [ query_string, setQueryString ] = useState(getQueryString(filters, search, page));
    const [ rows, setRows ] = useState([]);
    const options = initialOptions.filter(opt => opt.condition === undefined || opt.condition);
    const { data: page_req, is_loading, is_error, fetch } = useRequest({
        method: 'GET',
        url: url + (query_string ? `${url.includes('?') ? '&' : '?'}${query_string}` : ''),
    });
    const fetch_ref = useRef(fetch);
    const { t } = useI18n();

    useEffect(() => {
        fetch_ref.current = fetch;
    }, [ fetch ]);

    useEffect(() => {
        let aux = { ...initialFilters };

        Object.keys(aux).map(key => {
            aux[key].options.map((opt, i) => opt.selected = i === 0);
        });

        setFilters(aux);
    }, [ initialFilters ]);

    useEffect(() => {
        setQueryString(getQueryString(filters, search, page));
    }, [ filters, search, page ]);

    useEffect(() => {
        fetch();
    }, [ url, query_string ]);

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

    useImperativeHandle(ref, () => ({
        refetch: () => {
            setPage(1);
            fetch_ref.current();
        },
    }), []);

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

    /**
     * Single filter `<select>` bound to `filters[id]`; updates selection and resets to page 1 on change.
     * @param {Object} props
     * @param {string} props.id - Key in `filters`.
     * @returns {React.ReactElement}
     */
    const Filter = ({ id }) => {
        const filter = filters[id];

        return <div class="input-group">
            {filter.title && <label>{filter.title}</label>}
            <select onChange={e => {
                let aux = { ...filter };

                Object.keys(aux.options).map(opt_key => {
                    aux.options[opt_key].selected = String(aux.options[opt_key].key) === String(e.target.value);
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

    /**
     * Renders loading, error, empty, or the row list with optional selection and `rowOnClick` behavior.
     * @returns {React.ReactElement}
     */
    const Rows = () => {
        if (is_loading) {
            return <IconSpinner className="loading-icon"/>;
        }

        if (is_error) {
            return <p className="listing-title">{t('error_occurred')}</p>;
        }

        if (rows.length == 0) {
            return <p className="listing-title">{t('no_items_found')}</p>;
        }

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
});
