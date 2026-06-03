/**
 * Available events are: events: create, update, delete, select, pageChanged.
 * The data is in event.detail.
 *
 * @typedef {{
 *     id: {string},
 *     page: {number},
 *     intern: {Object},
 *     text: {string},
 *     label: {string},
 *     pos: {{x: number, y: number}},
 *     color: {Color},
 *     type: {Type},
 *     noDelete: {bool},
 * }} Annotation
 *
 * @typedef {string} Color // all hex forms are valid but names are not. E.g. `#FF003377` is valid but `green` is not.
 * @typedef {string} Type // 'marker', 'underline' or 'wave'
 *
 * @param {string} parent   id of the parent element to add the iframe
 * @param {string} viewer   url of the viewer html (source of iframe, without parameter)
 * @param {string} pdf      url of the pdf file to load
 * @param {{viewOnly: bool}} options init pdfjs in view only or not.
 *
 * @return {{
 *   on: {function(string, function(CustomEvent)): void},
 *   off: {function(string, function(CustomEvent)): void},
 *   getAll: {Promise<CustomEvent>},
 *   get: {function(string): Promise<Annotation>},
 *   update: {function(string): Promise},
 *   setAll: {function(Annotation[]): Promise},
 *   add: {function(Annotation): Promise},
 *   delete: {function(string, bool): Promise},
 *   selected: {function(): Promise<Annotation|null>},
 *   select: {function(string): Promise},
 *   currentPage: {function(): Promise<number>},
 *   destroy: {function(): void},
 *   rebuild: {function(): void},
 *   setViewOnly: {function(bool): Promise},
 *   setDefaultColor: {function(Color): Promise},
 *   buildBlob: {function(): Promise<Blob>},
 *   enableFreeFormHighlight: {function(bool): Promise},
 *   enableTextHighlight: {function(bool): Promise},
 *   setDrawMode: {function(Type): Promise},
 *   setLabel: {function(string, string): Promise},
 *   setText: {function(string, string): Promise},
 *   setColor: {function(string, Color): Promise},
 *   setType: {function(string, Type): Promise},
 *   setDeletable: {function(string, bool): Promise},
 * }}
 */
export default (parent, viewer, pdf, options = {}) => {
    let currentRequest = Promise.resolve();
    const t = new EventTarget();
    const dispatch = (name, detail = null) => t.dispatchEvent(new CustomEvent(name, {detail}));
    const nextId = ((i = 0) => () => ++i)();
    const pending = {};
    const frame = document.createElement('iframe');
    const ready = (function(){
        const ret = {};
        ret.promise = new Promise(function(resolve, reject){
            ret.resolve = resolve;
            ret.reject = reject;
        });
        return ret;
    })();
    const iframeParams = new URLSearchParams({file: pdf});
    if (options.viewOnly) {
        iframeParams.set('viewOnly', 'yes');
    }
    frame.src = viewer + '?' + iframeParams;
    frame.style.width = '100%';
    frame.style.height = '100%';
    parent.appendChild(frame);

    window.addEventListener('message', dispatchOrRespond);

    return {
        on: t.addEventListener.bind(t),
        off: t.removeEventListener.bind(t),
        getAll: () => requestUnsafe('getAll'),
        get: id => requestUnsafe('get', id),
        update: entry => request('update', entry),
        setAll: newOnes => request('setAll', newOnes),
        add: newOne => request('add', newOne),
        'delete': (id, enableUndo) => request('delete', id, enableUndo),
        selected: () => requestUnsafe('selected'),
        select: id => requestUnsafe('select', id),
        currentPage: () => requestUnsafe('currentPage'),
        destroy: () => {
            window.removeEventListener('message', dispatchOrRespond);
            frame.remove();
        },
        rebuild: () => {
            window.addEventListener('message', dispatchOrRespond);
            parent.appendChild(frame);
        },
        setViewOnly: viewOnly => request('viewOnly', viewOnly),
        setDefaultColor: color => request('setDefaultColor', color),
        buildBlob: () => request('buildBlob'),
	enableFreeFormHighlight: bool => request('enableFreeFormHighlight', bool),
        enableTextHighlight: bool => request('enableTextHighlight', bool),
        setDrawMode: mode => request('setDrawMode', mode),
        setLabel: (id, label) => request('setLabel', id, label),
        setText: (id, text) => request('setText', id, text),
        setColor: (id, color) => request('setColor', id, color),
        setType: (id, type) => request('setType', id, type),
        setDeletable: (id, deletable) => request('setDeletable', id, deletable),
    };

    function request(name, ...args)
    {
        // Void catch to resume new actions normally.
        // errors are not silenced with this, as the promise was already returned by a previous call to `request`.
        // This is only to ensure the previous promise has been processed (synchronization).
        return currentRequest = currentRequest.catch(Void).then(() => requestUnsafe(name, ...args));
    }

    function Void()
    {
    }

    function requestUnsafe(name, ...args)
    {
        return new Promise((ok, err) => {
            const id = nextId();
            pending[id] = {ok, err};
            ready.promise.then(() => frame.contentWindow.postMessage({id, name, args}));
        });
    }

    function respond(response)
    {
        if(!pending[response.id]){
            return;
        }
        const {ok, err} = pending[response.id];
        delete pending[response.id];
        (response.error ? err : ok)(response.value || 'aja');
    }

    function dispatchOrRespond(event)
    {
        if(event.source !== frame.contentWindow){
            return;
        }

        if(event.data.emit){
            if(event.data.emit.name === 'ready'){
                ready.resolve();
            }
            dispatch(event.data.emit.name, event.data.emit.detail);
            return;
        }

        if(event.data.response){
            respond(event.data.response);
            return;
        }
    }
}
