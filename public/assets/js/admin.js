const LOADING_ICON = '<svg class="loading-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" fill="none" stroke-width="10" r="36" stroke-dasharray="171 56"></circle></svg>';
function get(query) {
    return document.querySelectorAll(query)[0];
}

String.prototype.sprintf = function(...args) {
    let str = this;
    args.map(arg => str = str.replace('%s', arg));
    return str;
};

String.prototype.toSlug = function() {
    return this.toLowerCase().replace(/[^\w ]+/g, '').replace(/ +/g, '-');
};

Element.prototype.appendAfter = function(el) {
    this.parentNode.insertBefore(el, this.nextSibling);
};

Element.prototype.setLoading = function() {
    this.dataset.originalHtml = this.innerHTML;
    this.innerHTML = LOADING_ICON;
    this.classList.add('loading');
};

Element.prototype.resetState = function() {
    this.classList.remove('loading');
    if (this.dataset.hasOwnProperty('originalHtml')) {
        this.innerHTML = this.dataset.originalHtml;
        delete this.dataset.originalHtml;
    }
};

Element.prototype.getElementsBetween = function(el) {
    if (el === this) {
        return [];
    }

    const elements = [ ...this.parentElement.children ];
    const result = [];
    let start = null;
    let end = null;

    if (elements.indexOf(this) > elements.indexOf(el)) {
        start = el;
        end = this;
    } else {
        start = this;
        end = el;
    }

    let next = start.nextElementSibling;

    while (next && next !== end) {
        result.push(next);
        next = next.nextElementSibling;
    }

    return result;
};

class Snackbar {
    static #timeout = null;

    static show(msg = '', success = true) {
        let snackbar = get('#snackbar');

        if (snackbar.hasAttribute('show')) {
            this.hide();
            setTimeout(() => this.show(msg, success), 200);
            return;
        }

        if (!success) {
            snackbar.classList.add('error');
        }

        get('#snackbar > span').innerHTML = msg;
        snackbar.dataset.show = true;
        this.#timeout = setTimeout(() => this.hide(), 5000);
    }

    static hide() {
        let snackbar = get('#snackbar');
        clearTimeout(this.#timeout);
        delete snackbar.dataset.show;
        setTimeout(() => snackbar.classList.remove('error'), 200);
    }
}

class Form {
    static #getData(form_id, initial_data = {}) {
        let form_data = new FormData;

        Object.keys(initial_data).forEach(key => form_data.append(key, initial_data[key]));

        document.querySelectorAll(`#${form_id} *[name]`).forEach(el => {
            let type = el.getAttribute('type');
            let key = el.getAttribute('name');
            let value = el.value;

            if (type == 'checkbox') {
                if (el.hasAttribute('data-multiselect')) {
                    key += '[]';
                    value = el.checked ? el.getAttribute('value') : undefined;
                } else {
                    value = Number(el.checked);
                }
            } else if (type == 'file') {
                value = el.files[0];
            }

            if (typeof value !== 'undefined') {
                form_data.append(key, value);
            }
        });

        return form_data;
    }

    static #handleResponse(res, form_id) {
        if (res?.reload) {
            location.reload();
        }

        Object.keys(res?.errors ?? {}).forEach(key => {
            let input = get(`#${form_id} *[name="${key}"]`);
            if (!input) {
                return;
            }

            let err = document.createElement('span');
            err.classList.add('field-error');
            err.innerHTML = res.errors[key];
            input.appendAfter(err);
        });

        if (res?.success) {
            if (res?.msg !== null) {
                Snackbar.show(res?.msg ? res.msg : LANG.done);
            }
        } else if (res?.errors?.hasOwnProperty(0)) {
            Snackbar.show(res.errors[0], false);
        }

        return res;
    }

    static send(url, form_id = null, btn = null, extra_data = {}) {
        let btn_el = btn ? btn : event.target;

        if (btn_el) {
            if (btn_el.classList.contains('loading')) {
                return;
            }

            btn_el.setLoading();
        }

        if (form_id) {
            document.querySelectorAll(`#${form_id} .field-error`).forEach(el => el.remove());
        }

        return fetch(url, {
            method: 'POST',
            body: this.#getData(form_id, extra_data),
        })
            .then(res => {
                if (res.redirected) {
                    window.location.href = res.url;
                    return {};
                }

                return res.json();
            })
            .then(res => this.#handleResponse(res, form_id))
            .catch(() => {
                Snackbar.show(LANG.unexpected_error, false);
                return {};
            })
            .then(res => {
                if (btn_el) {
                    btn_el.resetState();
                }

                return res;
            });
    }

    static initFileInput(el) {
        el.querySelector('input[type="file"]').addEventListener('change', e => {
            el.querySelector('input[type="text"]').value = e.target.files[0] ? e.target.files[0].name : '';
        });
    }

    static initCharCounters() {
        document.querySelectorAll('*[data-char-count]').forEach(input => {
            let count_el = document.createElement('span');
            count_el.classList.add('char-counter');

            input.appendAfter(count_el);
            input.addEventListener('input', e => count_el.innerHTML = e.target.value.length + ' ' + LANG.characters);
            input.dispatchEvent(new Event('input'));
        });
    }
}

class Listing {
    static #select_mode = false;
    static #next_page_url = '';
    static #next_page = 1;
    static #prev_selected_row = null;

    static init() {
        window.addEventListener('keydown', e => {
            if (document.activeElement.tagName == 'INPUT') {
                return;
            }

            if ((e.key == 'Escape' && this.#select_mode) || e.key == 's') {
                this.toggleSelectMode(get('.batch-options-container > button'));
            }
        });
    }

    static setNextPageUrl(url) {
        this.#next_page_url = url;
    }

    static toggleSelectMode(btn_el) {
        let listing = get('#main-listing');
        let batch_options = get('#batch-options');
        let selected_items = get('#selected-items');

        if ('selectMode' in listing.dataset) {
            delete listing.dataset.selectMode;
            batch_options.style.visibility = 'hidden';
            this.getSelectedRows().map(el => this.toggleRow(el));
            this.#prev_selected_row = null;
            selected_items.style.visibility = 'hidden';
        } else {
            listing.dataset.selectMode = true;
            batch_options.style.visibility = 'visible';
            batch_options.querySelectorAll('button').forEach(el => el.setAttribute('disabled', true));
            selected_items.style.visibility = 'visible';
        }

        this.#select_mode = !this.#select_mode;
        btn_el.innerText = LANG[this.#select_mode ? 'done' : 'select'];
    }

    static toggleRow(row, event = null) {
        if (!this.#select_mode) {
            return;
        }

        if (event) {
            event.preventDefault();
            event.stopPropagation();

            if (event.shiftKey && this.#prev_selected_row) {
                [ this.#prev_selected_row, ...row.getElementsBetween(this.#prev_selected_row) ].forEach(el => {
                    if ('selected' in row.dataset) {
                        delete el.dataset.selected;
                    } else {
                        el.dataset.selected = true;
                    }
                });
            }
        }

        if ('selected' in row.dataset) {
            delete row.dataset.selected;
        } else {
            row.dataset.selected = true;
        }

        this.#prev_selected_row = row;
        let rows_selected_count = this.getSelectedRows().length;
        get('#batch-options').querySelectorAll('button').forEach(el => rows_selected_count > 0
            ? el.removeAttribute('disabled')
            : el.setAttribute('disabled', true));
        get('#selected-items').innerText = rows_selected_count == 0 ? '' : (rows_selected_count + ' Selected');
    }

    static getSelectedRows() {
        return [ ...document.querySelectorAll('.listing-row[data-selected="true"]') ];
    }

    static loadNextPage() {
        if (!this.#next_page) {
            return;
        }

        let listing = get('#main-listing');
        let total_items = get('#total-items');
        let btn_load_more = get('button.load-more');
        btn_load_more.setLoading();

        if (this.#next_page == 1) {
            this.getSelectedRows().map(el => this.toggleRow(el));
            listing.innerHTML = LOADING_ICON;
        }

        fetch(`${this.#next_page_url}${window.location.search}&page=${this.#next_page}`)
            .then(res => res.json())
            .then(res => {
                if (this.#next_page == 1) {
                    listing.innerHTML = res.html
                        ? res.html
                        : '<h3 class="empty">' + LANG.no_results + '</h3>';
                } else {
                    listing.insertAdjacentHTML('beforeend', res.html);
                }

                if (!res.next_page) {
                    btn_load_more.classList.add('hidden');
                    this.#next_page = false;
                } else {
                    btn_load_more.classList.remove('hidden');
                    this.#next_page++;
                }

                if (total_items && res.hasOwnProperty('count')) {
                    total_items.innerHTML = res.count + ' ' + LANG[res.count == 1 ? 'item' : 'items'];
                }
            })
            .finally(() => btn_load_more.resetState());
    }

    static refresh() {
        this.#next_page = 1;
        this.loadNextPage();
    }

    static handleResponse(res) {
        let open_dialog = get('.dialog.open');

        if (open_dialog) {
            Dialog.close(open_dialog);
        }

        Dropdown.close();

        if (res.success) {
            this.refresh();
        }
    }
}

class Dropdown {
    static #active_dropdown = null;

    static init() {
        let updateActiveDropdown = () => {
            const MARGIN = 4;

            if (!this.#active_dropdown) {
                return;
            }

            let btn_rect = this.#active_dropdown.original_btn.getBoundingClientRect();
            this.#active_dropdown.style.top = (btn_rect.top + btn_rect.height + MARGIN) + 'px';
            this.#active_dropdown.style.left = btn_rect.left + 'px';
            let dropdown_rect = this.#active_dropdown.getBoundingClientRect();

            if ((dropdown_rect.x + dropdown_rect.width) >= (window.innerWidth - MARGIN)) {
                this.#active_dropdown.style.left = ((btn_rect.x - dropdown_rect.width) + btn_rect.width) + 'px';
            }

            if (dropdown_rect.y + dropdown_rect.height >= (window.innerHeight - MARGIN)) {
                this.#active_dropdown.style.top = (btn_rect.y - dropdown_rect.height - MARGIN) + 'px';
            }
        }

        document.addEventListener('scroll', updateActiveDropdown);
        window.addEventListener('resize', updateActiveDropdown);
        document.addEventListener('click', e => {
            let dropdown_btn = e?.target?.closest('*[dropdown]');
            let dropdown = dropdown_btn?.querySelector('.dropdown-menu');

            if (this.#active_dropdown?.contains(e?.target)) {
                return;
            }

            this.close();
            if (this.#active_dropdown === dropdown) {
                return;
            }

            this.#active_dropdown = dropdown;

            if (this.#active_dropdown) {
                this.#active_dropdown.dataset.active = true;
                this.#active_dropdown.original_btn = dropdown_btn;
                document.body.appendChild(this.#active_dropdown);
                updateActiveDropdown();
            }
        }, true);
    }

    static close() {
        if (this.#active_dropdown) {
            delete this.#active_dropdown.dataset.active;
            this.#active_dropdown.original_btn.appendChild(this.#active_dropdown);
        }
    }
}

class Dialog {
    static show(container) {
        container.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    static close(container) {
        container.classList.remove('open');
        document.body.style.overflow = 'auto';
    }
}

class ImageDialog {
    static #input_el = null;
    static #img_el = null;
    static #dialog_container = null;
    static #dialog_el = null;
    static #content_path = '';
    static #current_path = '';

    static init(dialog_container, input_el, img_el, content_path) {
        this.#dialog_container = dialog_container;
        this.#dialog_el = dialog_container.querySelector('div');
        this.#input_el = input_el;
        this.#img_el = img_el;
        this.#content_path = content_path;
        this.#current_path = '';

        img_el.addEventListener('click', () => {
            this.#dialog_el.innerHTML = LOADING_ICON;
            Dialog.show(this.#dialog_container);
            this.setImagePage(content_path);
        });

        this.#dialog_el.addEventListener('dragover', event => {
            event.preventDefault();
        }, false);

        this.#dialog_el.addEventListener('drop', event => {
            event.preventDefault();

            this.#dialog_el.innerHTML = LOADING_ICON;
            let data = new FormData();
            data.append('csrf', csrf_token);
            Array.from(event.dataTransfer.files).map(file => data.append('file[]', file));

            fetch('/admin/media/upload?path=' + this.#current_path, {
                method: 'POST',
                body: data,
            })
            .then(res => res.json())
            .then(res => {
                this.setImagePage(this.#current_path);
                if (res.errors && res.errors.hasOwnProperty(0)) {
                    alert(res.errors[0]);
                }
            })
            .catch(() => alert(LANG.unexpected_error));
        });
    }

    static setImagePage(path) {
        this.#current_path = path;
        fetch('/admin/image_dialog?path=' + path)
            .catch(() => alert(LANG.unexpected_error))
            .then(async res => {
                if (res.status != 200) {
                    alert(LANG.unexpected_error);
                    Dialog.close(this);
                    return;
                }

                this.#dialog_el.innerHTML = await res.text();
            });
    }

    static setImage(path) {
        this.#input_el.value = path;
        this.#img_el.src = '/' + this.#content_path + '/' + path;
        this.#img_el.classList.remove('empty-img');
    }

    static clearImage() {
        this.#input_el.value = '';
        this.#img_el.src = '/public/assets/no-image.svg';
        this.#img_el.classList.add('empty-img');
    }

    static close() {
        Dialog.close(this.#dialog_container);
    }
}
