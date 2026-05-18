run();

function run()
{
    window.localStorage.removeItem('pdfjs.history');
    window.localStorage.removeItem('pdfjs.preferences');
    setup(forwardEvent, actions => {
        window.addEventListener('message', event => {
            then(
                actions[event.data.name](...event.data.args),
                value => window.parent.postMessage({response: {id: event.data.id, value}})
            );
        });
    });
    
    window.addEventListener('beforeunload', () => {
        PDFViewerApplication.pdfViewer.pdfDocument.annotationStorage.resetModified();
    });

    function forwardEvent(name, detail)
    {
        window.parent.postMessage({emit: {name, detail}});
    }
}

function setup(dispatch, ready){
    let entries = [];
    let selecting = null; // Used to streamline select when switching editor modes.
    let updating = null; // Used to streamline updating, to prevent bogus create & delete events.
    let deletedIds = []; // Used to prevent 'delete' events that are triggered manually.
    let lastDeleted = {}; // For undo to work
    let currentMode = 'marker';
    const selected = state(null, (oldOne, newOne) => {
        selecting = null;
        const ret = (oldOne || {}).returnPending;
        ret && ret();
    });
    const uiManager = proc => pdfOnInit(x => proc(x.uiManager));
    const switchPage = switchPageWhenReady();

    uiManager(manager => {
        pdfOn('annotationeditorparamschanged', checkForChanges);
        pdfOn('switchannotationeditorparams', checkForChanges);
        pdfOnPageChanging(pageChanging);

        const actions = {
            getAll: () => entries.map(externEntry),
            get: id => externEntry(entries.find(e => e.id === id)),
            setAll: newOnes => {
                entries.forEach(x => deleteEntry(x)); // Don't pass index as enableUndo
                entries = [];
                newOnes.forEach(actions.add);
            },
            add: newOne => {
                const id = newOne.id || uuid();
                const page = typeof newOne.page === 'number' ? newOne.page : pdfCurrentPageIndex();
                const entry = {
                    id,
                    page,
                    text: newOne.text,
                    label: newOne.label,
                    editor: null,
                    intern: newOne.intern,
                    color: newOne.color,
                    type: newOne.type || (newOne.intern.underline ? 'underline' : 'marker'),
                    noDelete: newOne.noDelete,
                    pos: newOne.pos,
                };
                entries.push(entry);
                sync(entry, 'create', layer => {
                    return layer.deserialize(newOne.intern).then(editor => {
                        entry.editor = editor;
                        if(entry.text){
                            editor.contents = entry.text;
                        }
                        if (entry.color) {
                            entry.editor.updateParams(pdfjsLib.AnnotationEditorParamsType.HIGHLIGHT_COLOR, entry.color);
                        }
                        pdfAddEditorToLayerNoFocus(layer, entry.editor, () => {
                            if(entry.label){
                                entry.labelDiv = createLabelDiv(entry.label);
                                editor.getHightligtDiv().parentNode.appendChild(entry.labelDiv);
                            }
                        });
                        adjustEditor(editor, entry.type, entry.color);
                    });
                });
            },
            'delete': (id, enableUndo) => {
                entries = entries.filter(x => {
                    if(x.id === id){
                        deleteEntry(x, enableUndo);
                        return false;
                    }
                    return true;
                });
            },
            update: extern => {
                const isSelected = (selected() || {}).id === extern.id;
                updating = extern.id;
                actions.delete(extern.id);
                actions.add(extern);
                if(isSelected){
                    return actions.select(extern.id);
                }
            },
            selected: () => selected() ? externEntry(selected()) : null,
            select: id => {
                const entry = entryById(id);
                selected(entry);
                const ret = sync(entry, 'select', () => {
                    if(PDFViewerApplication.pdfViewer.annotationEditorMode !== PDF_EDIT_MODE()){
                        selecting = entry.editor;
                        entry.editor.annotationElementId = entry.id;
                        actions.viewOnly(false);
                    }else{
                        manager.setSelected(entry.editor);
                    }
                    updateDeletable(entry);
                });
                if(entry.page !== pdfCurrentPageIndex()){
                    switchPage(entry.page);
                }

                return ret;
            },
            currentPage: pdfCurrentPageIndex,
            viewOnly: viewOnly => {
                pdfSwitchToMode(viewOnly ? PDF_VIEW_MODE() : PDF_EDIT_MODE());
                document.querySelector('#editorHighlight').classList[viewOnly ? 'add' : 'remove']('annotate-pdf-hide');
            },
            setDefaultColor: color => {
                pdfjsLib.HighlightEditor.updateDefaultParams(
                    pdfjsLib.AnnotationEditorParamsType.HIGHLIGHT_COLOR,
                    color
                );
            },
            buildBlob: () => {
                const origPage = pdfCurrentPageIndex();
                return entries.reduce((p, entry) => {
                    if (entry.editor) {
                        return p;
                    }
                    return p.then(() => {
                        switchPage(entry.page);
                        return sync(entry, 'ensureRendered', Void);
                    });
                }, Promise.resolve()).then(() => switchPage(origPage)).then(buildBlobNoWait);
            },
	    enableFreeFormHighlight: bool => {
		document.querySelector('#viewer').classList[bool ? 'remove' : 'add']('disable-freeform-highlight');
		manager.disableFreeForm = !bool;
	    },
            enableTextHighlight: bool => {
                document.querySelector('#viewer').classList[bool ? 'remove' : 'add']('disable-text-highlight');
		PDFViewerApplication.eventBus.disableTextHighlight = !bool;
            },
            setDrawMode: mode => new Promise((ok, err) => {
                if (validDrawTypes().includes(mode)) {
                    currentMode = mode;
                    ok();
                } else {
                    err('Invalid mode given: ' + mode);
                }
            }),
            setLabel: (id, label) => {
                const entry = entries.find(e => e.id === id);
                sync(entry, 'setLabel', () => {
                    entry.label = label;
                    if (entry.labelDiv) {
                        entry.labelDiv.textContent = label;
                    } else {
                        entry.labelDiv = createLabelDiv(entry.label);
                        entry.editor.getHightligtDiv().parentNode.appendChild(entry.labelDiv);
                    }
                });
            },
            setText: (id, text) => {
                const entry = entries.find(e => e.id === id);
                sync(entry, 'setText', () => {
                    entry.text = text;
                    entry.editor.contents = text;
                });
            },
            setColor: (id, color) => {
                const entry = entries.find(e => e.id === id);
                sync(entry, 'setColor', () => {
                    entry.color = color;
                    entry.editor.updateParams(pdfjsLib.AnnotationEditorParamsType.HIGHLIGHT_COLOR, color);
                    if (entry.type === 'wave') {
                        entry.editor.getPathNode().setAttribute('stroke', color);
                    }
                });
            },
            setType: (id, type) => {
                if (!validDrawTypes().includes(type)) {
                    throw new Error('Invalid draw type: ' + type);
                }
                const entry = entries.find(e => e.id === id);
                sync(entry, 'setType', () => {
                    entry.editor.edutiekType = type;
                    entry.type = type;
                    adjustEditor(entry.editor, entry.type, entry.color);
                });
            },
            setDeletable: (id, bool) => {
                const entry = entries.find(e => e.id === id);
                entry.noDelete = !bool;
                updateDeletable(entry);
            },
        };

        actions.viewOnly(Boolean(new URLSearchParams(window.location.search).get('viewOnly')));
        ready(actions);
        PDFViewerApplication.viewsManager.setInitialView(0);
        dispatch('ready');

        function deleteEntry(entry, enableUndo)
        {
            const undo = () => {
                manager.addEditorToLayer(entry.editor);
            };
            const cmd = () => {
                manager._editorUndoBar?.show(undo, entry.editor.editorType);
                entry.editor.remove();
            };
            deletedIds.push(entry.id);
            if (enableUndo && entry.editor) {
                lastDeleted = {internId: entry.editor.id, id: entry.id};
                manager.addCommands({
                    cmd,
                    undo,
                    mustExec: true,
                });
            } else {
                entry.editor?.remove();
            }
            const ret = entry.returnPending;
            ret && ret();
        }

        function checkForChanges(){
            const page = pdfCurrentPageIndex();
            const usedIds = Array.from(manager.getEditors(page)).map(createOrUpdateEntry.bind(null, page));
            const isUsed = x => x.page !== page || usedIds.includes(x.id) || (x.pending || []).length || updating === x.id;
            const deleted = entries.filter(x => !isUsed(x));
            entries = entries.filter(isUsed);
            updating = null;
            deleted.forEach(x => {
                if (!deletedIds.includes(x.id)) {
                    lastDeleted = {internId: x.editor.id, id: x.id};
                    dispatch('delete', externEntry(x));
                }
            });
            deletedIds = deletedIds.filter(id => !deleted.find(x => x.id === id));
            updateSelection();
        }

        function pageChanging(){
            const page = pdfCurrentPageIndex();
            dispatch('pageChanged', page);
        }

        function createOrUpdateEntry(page, editor)
        {
            const newData = pdfSerializeEditor(editor);
            const s = JSON.stringify(newData);
            let entry = entryByEditor(editor);

            if(!entry){
                Promise.all(entries.filter(x => x.page === page).map(x => sync(x, 'checkCreate', Void))).then(() => {
                    if(entryByEditor(editor)){return;}
		    adjustEditor(editor, currentMode);
                    const id = lastDeleted.internId === editor.id ? lastDeleted.id : uuid();
                    const entry = {id, page, editor, intern: pdfSerializeEditor(editor), type: currentMode};
                    const extern = externEntry(entry);
                    entries.push(entry);
                    dispatch('create', extern);
                    selected(entry);
                    dispatch('select', extern);
                });
                return null;
            }else if(s !== JSON.stringify(entry.intern)){
                // These are null -> NaN and rounding issues that don't need to be propagated.
                const ignore = arrayEquals(
                    ['outlines', 'rect'],
                    Object.keys((diff(newData, entry.intern) || {}).Object || {})
                );
                entry.intern = newData;
                if(!ignore){
                    dispatch('update', externEntry(entry));
                }
            }

            return entry.id;
        }

        function updateSelection()
        {
            if(selecting && manager.firstSelectedEditor === selecting){
                selecting = null;
            }else if(
                (!selecting || manager.firstSelectedEditor)
                    && manager.firstSelectedEditor !== ((selected() || {}).editor || undefined)
            ){
                const entry = manager.firstSelectedEditor ? entryByEditor(manager.firstSelectedEditor) : null;
                if(entry || selected()){
                    selected(entry);
                    dispatch('select', entry ? externEntry(entry) : null);
                    if (entry) {
                        updateDeletable(entry);
                    }
                }
            }
        }

        function entryByEditor(editor)
        {
            return entries.find(x => x.editor === editor);
        }

        function entryById(id)
        {
            return entries.find(x => x.id === id);
        }
    });
}

function buildBlobNoWait()
{
    return new Promise(ok => {
        const proc = data => {PDFViewerApplication.eventBus.off(proc); ok(data);};
        PDFViewerApplication.eventBus.on('edutiekDownload', proc);
        PDFViewerApplication.eventBus.dispatch('download');
    })
}

function updateDeletable(entry)
{
    if (!entry.editor || !entry.editor._editToolbar) {
        return;
    }
    entry.editor._editToolbar.div.classList[entry.noDelete ? 'add' : 'remove']('annotate-pdf-hide');
}

function createLabelDiv(label)
{
    const d = document.createElement('div');
    d.classList.add('annotation-label');
    d.textContent = label;
    return d;
}

function adjustEditor(editor, mode, color)
{
    editor.edutiekType = mode;
    editor.originalPath ||= editor.getPathNode().getAttribute('d');
    changeSvg(editor, mode, color);
}

function validDrawTypes()
{
    return ['marker', 'underline', 'wave'];
}

function changeSvg(editor, mode, color)
{
    if(!editor._mustFixPosition){ // If it is a freeform highlight.
        return;
    }

    color = color || pdfjsLib.HighlightEditor._defaultColor;
    switch(mode){
    case 'marker':    return changeSvgToMarker(editor, color);
    case 'underline': return changeSvgToUnderline(editor, color);
    case 'wave':      return changeSvgToWave(editor, color);
    default:          throw new Error('Invalid type given to change SVG');
    }
}

function changeSvgToMarker(editor, color)
{
    const node = editor.getPathNode();
    node.setAttribute('d', editor.originalPath);
    node.removeAttribute('fill');
    node.removeAttribute('stroke-width');
    node.removeAttribute('stroke');
}

function changeSvgToUnderline(editor, color)
{
    const pathNode = editor.getPathNode();
    let newS = '';
    let s = editor.originalPath;
    const len = (s.split('M').length - 1);
    let m;
    let skip = false;
    const startY = Number(s.match(/M *[0-9.]+ +([0-9.]+)/)[1]);
    const shift = startY * 0.9; // 90% of original size, as this is 0 to startY.
    while(m = s.match(/V([0-9.]+)/)){
        if (skip) {
            newS += s.substring(0, m.index + m[0].length);
        } else {
            newS += s.substring(0, m.index) + 'V' + (Number(m[1]) + shift);
        }
        s = s.substring(m.index + m[0].length);
        skip = !skip;
    }
    pathNode.setAttribute('d', newS);
    pathNode.removeAttribute('stroke-width');
    pathNode.removeAttribute('stroke');
    pathNode.removeAttribute('fill');
}

function changeSvgToWave(editor, color)
{
    const pathNode = editor.getPathNode();
    const svgNode = editor.getSvgNode();
    const width = editor.getSvgNode().getBoundingClientRect().width;
    const origPath = editor.originalPath;
    const startY = Number(origPath.match(/M *([0-9.]+) +([0-9.]+)/)[2]);
    const parts = origPath.split('M').slice(1);
    const len = parts.length;
    const pitch = 0.15 * startY;
    const step = (1 / width) * 7;
    const lineHeight = 1 / len;
    const newPath = parts.map((part, i) => {
        const [x, y, v1, h, v2] = part.match(/([0-9.]+) +([0-9.]+) +V *([0-9.]+) +H *([0-9.]+) +V *([0-9.]+)/).slice(1).map(x => Number(x));
        const yy = y - pitch;
        let s = `M${x} ${yy}`;
        let dir = -1;
        let n = x;
        while (n + step < h) {
            s += ` Q${n + (step / 2)} ${(yy) + (dir * pitch)} ${n + step} ${yy}`;
            n += step;
            dir = -dir;
        }
        return s;
    }).join(' ');
    pathNode.setAttribute('d', newPath);
    pathNode.setAttribute('stroke', color);
    pathNode.setAttribute('stroke-width', '1.4');
    pathNode.setAttribute('fill', 'transparent');
}

function externEntry(entry)
{
    return {
        id: entry.id,
        page: entry.page,
        intern: entry.intern,
        text: entry.text,
        label: entry.label,
        pos: entry.editor ? {x: entry.editor.x, y: entry.editor.y} : entry.pos,
        color: entry.color || ('#' + entry.intern.color.map(c => (c < 16 ? '0' : '') + c.toString(16)).join('')),
        type: entry.type,
        noDelete: Boolean(entry.noDelete),
    };
}

function uuid()
{
    return new Date().getTime() + "-" + Math.round(100000000+Math.random()*999999999);
}

function then(p, proc)
{
    return Promise.resolve(p).then(proc);
}

/**
 * Synchronize actions for entries.
 * When creating, updating, selecting annotations in the UI it is not
 * always guaranteed that the page the annotation is displayed on is
 * currently rendered, so the corresponding methods for this won't
 * work (e.g. `pdfEditLayer(page)` returns null).  This function
 * checks if the current AnnotationEditorLayer for the entry's page is
 * available and queues the action until the corresponding
 * AnnotationEditorLayer becomes available. The given name is used to
 * drop previous actions of the same name (so that e.g. 2 queued
 * update's will result in one update only).  To kill the queued
 * actions (e.g. when the entry got deleted in the meantime) call
 * entry.returnPending().  Queued actions will be run after the whole
 * chain of the previous action has been resolved.
 * This function returns a promise which resolves when the action has
 * either been completed or the action has been killed.
 *
 * @example
 * ```js
 * const dummy = {page: 3};
 * sync(dummy, 'create', layer => new Promise(ok => setTimeout(() => console.log('create end') || ok(), 1000)));
 * sync(dummy, 'update', layer => console.log('will not be displayed'));
 * sync(dummy, 'update', layer => console.log('will be displayed'));
 * ````
 * This function ensures that first "create end" will be displayed and *then* "will be displayed".
 *
 * @example:
 * ```js
 * sync(dummy, 'create', layer => new Promise(ok => setTimeout(() => console.log('create end') || ok(), 1000)));
 * sync(dummy, 'update', layer => console.log('will be displayed'));
 * dummy.returnPending();
 * ```
 * Unless `pdfEditLayer(dummy.page)` is available when this code runs nothing will be displayed.
 * If `pdfEditLayer(dummy.page)` *is* available, *only* "create end" will be displayed.
 *
 * @return {Promise}
 */
function sync(entry, name, action)
{
    const pending = (entry.pending || []).length;
    if(!pending){
        const layer = pdfEditLayer(entry.page);
        if(layer){
            const ret = enqueue();
            dequeue(layer);
            return ret;
        }

        const ret = enqueue();
        pdfOn('annotationeditorlayerrendered', layerRendered);
        entry.returnPending = () => {
            entry.pending.forEach(p => p?.return());
            entry.pending = [];
            delete entry.returnPending;
            pdfOff('annotationeditorlayerrendered', layerRendered);
        };

        return ret;
    }

    return enqueue();

    function kill(pending)
    {
        if(entry.pending.length === 1){
            pdfOff('annotationeditorlayerrendered', layerRendered);
        }
        entry.pending = entry.pending.filter(x => x !== pending);
        pending.return();
    }

    function layerRendered(event)
    {
        if(event.pageNumber - 1 === entry.page){
            const todo = entry.pending;
            const layer = pdfEditLayer(entry.page);
            dequeue(layer);
            pdfOff('annotationeditorlayerrendered', layerRendered);
        }
    }

    function enqueue()
    {
        return new Promise(function(resolve){
            const pending = {run: action, return: resolve, name};
            entry.pending = (entry.pending || []).filter(pending => {
                if(pending?.name === name){
                    kill(pending);
                    return false;
                }
                return true;
            });
            entry.pending.push(pending);
        });
    }

    function dequeue(layer)
    {
        if(entry.pending.length === 0){
            return Promise.resolve();
        }
        const head = entry.pending[0];
        entry.pending = [null].concat(entry.pending.slice(1));
        const ret = head.run(layer);
        head.return(ret);

        return then(ret, () => {
            entry.pending = entry.pending.slice(1);
            dequeue(layer);
        });
    }
}

function pdfOnInit(thunk)
{
    pdfReady(function(){
         pdfOn('annotationeditoruimanager', thunk);
    });
}

function pdfReady(proc)
{
    window.addEventListener('DOMContentLoaded', tryit);

    function tryit()
    {
        if(window.PDFViewerApplication && PDFViewerApplication.pdfViewer && PDFViewerApplication.pdfViewer.eventBus){
            proc();
        }else{
            setTimeout(tryit, 10);
        }
    }
}

/**
 * Return the AnnotationEditorLayer for the given page.
 * This functions returns null if the page has not been rendered (or
 * is off screen again).
 * Use the function sync, to run code as soon as the
 * AnnotationEditorLayer for a specific page is available.
 *
 * @return {AnnotationEditorLayer|null}
 */
function pdfEditLayer(page)
{
    return ((PDFViewerApplication.pdfViewer._pages[page] || {}).annotationEditorLayer || {}).annotationEditorLayer;
}

/**
 * Serialize an editor so that it can be deserialized again.
 * Using
 * `AnnotationEditorLayer.deserialize(HighlightEditor.serialize())`
 * directly doesn't work.
 * See also: HighlightEditor.deserialize.
 *
 * @param {HighlightEditor} editor
 * @return {Object}
 */
function pdfSerializeEditor(editor)
{
    // The annotationElementId is needed to keep an element
    // highlighted when switching modes (mode switching is
    // asynchronous with no way to wait for it). But the
    // annotationElementId breaks the serialization.
    const old = editor.annotationElementId;
    editor.annotationElementId = null;
    const obj = editor.serialize();
    editor.annotationElementId = old;

    if(obj.quadPoints){ // text drawing
        // If this is a text drawing the `quadPoints` property is
        // returned as a plain object but the
        // HighlightEditor.deserialize method expects this to be an
        // array...
        obj.quadPoints = Object.values(obj.quadPoints);
    }else{ // free hand drawing
        // This is guess work:
        // The HighlightEditor.deserialize method checks for a
        // inkLists property of the form: [0: [number, number, ...]].
        // When serializing a free hand drawing, the inkList property
        // is null but the path `outlines.points` has the same
        // structure as the expected inkList propery, so we set the
        // inkList to this path.
        // This works without any issues.
        obj.inkLists = obj.outlines.points;
    }

    return obj;
}

function pdfCurrentPageIndex()
{
    return PDFViewerApplication.pdfViewer.currentPageNumber - 1;
}

function pdfSwitchToPageIndex(page)
{
    // PDFViewerApplication.eventBus is the same object as PDFViewerApplication.pdfViewer.eventBus
    PDFViewerApplication.eventBus.dispatch('pagenumberchanged', {value: page + 1});
}

function pdfOnPageChanging(proc)
{
    PDFViewerApplication.eventBus.on('pagechanging', proc);
}

function pdfOn(n, proc)
{
    PDFViewerApplication.eventBus.on(n, proc);
}

function pdfOnce(n, proc)
{
    PDFViewerApplication.eventBus.on(n, proc, {once: true});
}

function pdfOff(n, f)
{
    PDFViewerApplication.eventBus.off(n, f);
}

function PDF_EDIT_MODE()
{
    return 9; // view / select mode = 0
}

function PDF_VIEW_MODE()
{
    return 0;
}

function pdfSwitchToMode(mode, editId = null)
{
    PDFViewerApplication.eventBus.dispatch('switchannotationeditormode', {mode, editId});
}

/**
 * This function adds a HighlightEditor to a AnnotationEditorLayer and
 * ensuring the added editor doesn't get focused. Without this
 * function it is unpredictable wether or not the editor gets selected.
 *
 * @param {AnnotationEditorLayer} layer
 * @param {HighlightEditor} editor
 */
function pdfAddEditorToLayerNoFocus(layer, editor, onRender)
{
    onRender ||= Void;
    // Temporary overwrite prototype chain.
    editor.render = () => {
        delete editor.render;
        const ret = editor.render();
        editor.div.focus = Void; // Same again.
        onRender();
        return ret;
    };
    layer.add(editor);
    delete editor.div.focus;
}

function state(initValue, onUpdate = Void)
{
    return (...args) => {
        if(args.length === 0){
            return initValue;
        }
        onUpdate(initValue, args[0]);
        initValue = args[0];
    };
}

function switchPageWhenReady()
{
    let call = state(Void);
    pdfReady(() => pdfOnce('pagesloaded', () => {
        call()();
        call = proc => proc();
    }));

    return nr => {
        call(() => pdfSwitchToPageIndex(nr));
    };
}

function arrayEquals(left, right)
{
    return diff(left, right) === null;
}

function diff(left, right)
{
    if(left === right){
        return null;
    }
    const t = typeof left;
    if(t !== typeof right){
        return {leftType: t, rightType: typeof right};
    }
    if(t !== 'object'){
        return {left, right};
    }

    if(left instanceof Array){
        if(!(right instanceof Array)){
            return {leftType: 'Array', rightType: right.constructor.name || 'dunno'};
        }
        const diffs = {};
        for(let i = 0; i < Math.max(left.length, right.length); i++){
            const d = diff(left[i], right[i]);
            if(d !== null){
                diffs[i] = d;
            }
        }
        if(Object.values(diffs).length){
            return {Array: diffs};
        }
        return null;
    }

    const keys = new Set();
    Object.keys(left).concat(Object.keys(right)).forEach(keys.add.bind(keys));

    const diffs = {};
    keys.forEach(key => {
        const d = diff(left[key], right[key]);
        if(d !== null){
            diffs[key] = d;
        }
    });

    if(Object.values(diffs).length){
        return {Object: diffs};
    }

    return null;
}

function overwriteMethodOnce(obj, method, instead = Void)
{
    obj[method] = () => {
        delete obj[method];
        return instead(obj[method]);
    };
}

function Void(){}
