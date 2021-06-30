
(function($) {

    var moduleName = 'myDataTable';
    function fitConfigPage(page, pagesize, total) {
        var pageNr = 1;
        if (!isNaN(+page) && pagesize > 0 && total > 0) {
            pageNr = Math.max(1, +page);

            if (pageNr > 1) {
                pageNr = Math.min( pageNr, Math.ceil( total / pagesize) );
            }
        }
        if (0) {
            console.log('myDataTable.js #14 fitConfigPage(page, pagesize, total)',
                {page, pagesize, total }, { pageNr });
        }
        return pageNr;
    }

    function fitConfig(props, data) {
        var config = $.extend( {}, props );
        if (!("key" in config)) config.key = null;
        if (!("fields" in config) && data.length) {
            config.fields = {};
            config.rownumbers = true;
            for(var fi in data[0]) config.fields[fi] = fitConfigField( fi );
        } else {
            config.fields = fitConfigFields( config.fields )
        }

        if (!("title" in config)) config.title = '';
        if (!("colfilters" in config)) config.colfilters = false;
        if (!("rownumbers" in config)) config.rownumbers = false;
        config.rowactions =(config.editable || config.insertable || config.deletable || config.openDoc);
        if (!("rowformatter" in config) || typeof config.rowformatter !== 'function') config.rowformatter = null;

        if (!("pagesize" in config) || isNaN( +config.pagesize) ) config.pagesize = 0;
        if (!("page" in config)) config.page = 1;
        if (!("onRendered' in config")) config.onRendered = null;
        if (!("footer' in config")) config.footer = [];
        if (!("showFooter" in config)) config.showFooter = config.footer && config.footer.length > 0;

        else config.page = fitConfigPage(config.page, config.pagesize, data.length );

        return config;
    }

    function fitConfigField(col, field) {
        if (typeof field === 'string' || typeof field === 'number') {
            return { field, name: field, colspan: 1, hidden: false, formatter: null, colfilter: true };
        }

        if (typeof field !== 'object') {
            return { field: col, name: null, colspan:  0, hidden: true };
        }

        var cnfField = $.extend({}, field);

        if (!("name" in cnfField)) {
            cnfField.name = col;
        }

        if (!("field" in cnfField)) {
            cnfField.field = col;
        }

        if (!("colspan" in cnfField)) {
            cnfField.colspan = 1;
        }

        if (!("hidden" in cnfField)) {
            cnfField.hidden = false;
        }

        if (!("editable" in cnfField)) {
            cnfField.editable = false;
        }

        if (!("formatter" in cnfField) || (typeof cnfField.formatter !== 'function' && !(cnfField.formatter in $.fn[moduleName].formatters))) {
            cnfField.formatter = null;
        }

        if (!("colfilter" in cnfField) || typeof cnfField.colfilter !== 'function') {
            cnfField.colfilter = ("colfilter" in cnfField) && !!cnfField.colfilter;
        }

        return cnfField;
    }

    function fitConfigFields(fields) {

        var cnfFields = $.extend({}, fields);
        for(var _col in cnfFields) {
            cnfFields[ _col ] = fitConfigField(_col, cnfFields[ _col ]);
        }
        return cnfFields;
    }

    function isPromise(obj) {
        return (typeof obj === 'object' && obj !== null && obj instanceof Promise );
    }

    function isUsableAsPromise(obj) {
        return (typeof obj === 'object' && obj !== null &&
            'then' in obj && typeof obj.then === 'function' &&
            'catch' in obj && typeof obj.catch === 'function' &&
            'finally' in obj && typeof obj.finally === 'function'
        );
    }

    function isUsableAsPromiseWithThenCatch(obj) {
        return (typeof obj === 'object' && obj !== null &&
            'then' in obj && typeof obj.then === 'function' &&
            'catch' in obj && typeof obj.catch === 'function'
        );
    }

    function isConvertableToPromiseWithThenCatchFinally(obj) {
        return (typeof obj === 'object' && obj !== null &&
            (
                ('then' in obj && typeof obj.then === 'function')
                || ('done' in obj && typeof obj.done === 'function')
            ) &&
            (
                ('catch' in obj && typeof obj.catch === 'function')
                || ('fail' in obj && typeof obj.fail === 'function')
            ) &&
            (
                ('finally' in obj && typeof obj.finally === 'function')
                || ('always' in obj && typeof obj.always === 'function')
            )
        );
    }

    function makeUsableAsPromise(obj) {
        if (isPromise(obj) || isUsableAsPromise(obj)) {
            return obj;
        }

        if (isConvertableToPromiseWithThenCatchFinally(obj)) {
            if (!'then' in obj && 'done' in obj && typeof obj.done === 'function') {
                obj.then = obj.done;
            }
            if (!'catch' in obj && 'fail' in obj && typeof obj.fail === 'function') {
                obj.catch = obj.fail;
            }
            if (!'finally' in obj && 'always' in obj && typeof obj.always === 'function') {
                obj.finally = obj.always;
            }
        }

        return obj;
    }

    var addComparer = function(_obj, comparer) {
        if (typeof _obj !== 'object' || !('config' in _obj)) {
            alert("Falscher Parameter für _obj in addComparer\n" + JSON.stringify({_obj, comparer }));
            return;
        }
        if (!('multiSortBy' in _obj.config) || !Array.isArray(_obj.config.multiSortBy)) {
            _obj.config.multiSortBy = [ comparer ];
            return true;
        }

        if (_obj.config.multiSortBy.length === 0) {
            _obj.config.multiSortBy[0] = comparer;
            return true;
        }

        for(var i = 0; i < _obj.config.multiSortBy.length; i++) {
            var _itm = _obj.config.multiSortBy[i];
            if (_itm.field === comparer.field) {
                _obj.config.multiSortBy[i] = comparer;
                return true;
            }
        }
        _obj.config.multiSortBy.push( comparer );
        return true;
    };

    var createFncSortCompare = function(_obj, field, dir) {
        var isAsc = (arguments.length < 2 || dir.toString().toUpperCase() !== 'DESC');

        if (_obj.config.colIndex === 'NUM' && _obj.config.colNames.length > 0) {
            field = _obj.config.colNames.indexOf( field );
        }

        var toggleSortDir = isAsc ? 1 : -1;

        var comparer = function(field, toggleFaktor, a, b) {
            if ( (!(field in a) && !(field in b)) || a[field] === b[field]) {
                return 0;
            }

            if (!(field in a) || !(field in b)) {
                console.error("Order-Field " + field + " not found", {a, b});
                return toggleFaktor * (!(field in a) ? -1 : 1);
            }

            if (!isNaN(a[field]) && !isNaN(b[field]) ) {
                var av = +a[field], bv = +b[field];
                return (av === bv) ? 0 : (toggleFaktor * (av <= bv ? -1 : 1));
            }

            if (typeof a[field] === 'string' && typeof b[field] === 'string') {
                return toggleFaktor * a[field].localeCompare(b[field]);
            }

            if (a[field] instanceof Date && b[field] instanceof Date) {
                var av = a[field].getTime(), bv = b[field].getTime();
                return (av === bv) ? 0 : (toggleFaktor * (av < bv ? -1 : 1));
            }

            var av = (a[field] !== null && a[field] !== undefined) ? a[field].toString() : '';
            var bv = (b[field] !== null && b[field] !== undefined) ? b[field].toString() : '';
            // sortCase = 'Other-Compare';
            return toggleFaktor * av.localeCompare(bv);
        };
        return comparer.bind(null, field, toggleSortDir);

    };


    var _getters = {
        getElm: function(_obj) { return _obj.elm; },
        getTable: function(_obj) { return _obj.tbl; },
        getThead: function(_obj) { return $(_obj.tbl).find('thead'); },
        getTBody: function(_obj) { return $(_obj.tbl).find('tbody'); },
        getTotal: function(_obj) { return (_obj.useFilterResult) ? _obj.result.length : _obj.data.length; },
        getPageNr: function(_obj) { return _obj.config.page; },
        getPageInfo: function(_obj) {
            var numPages = this.getNumPages(_obj);
            var page = _obj.config.page;
            var pageSize = _obj.config.pagesize;
            var total = this.getTotal(_obj);
            var offset = 1 + (_obj.config.page - 1) * _obj.config.pagesize;
            var end = Math.min(total, _obj.config.page * _obj.config.pagesize);
            var text = offset + " bis " + end + " von " + total + " Einträgen";
            return {
                page,
                offset,
                end,
                total,
                numPages,
                pageSize,
                text
            };
        },
        getNumPages: function(_obj) { return Math.ceil(this.getTotal(_obj) / _obj.config.pagesize); },
        getConfig: function(_obj) { return $.extend({}, JSON.parse(JSON.stringify(_obj.config))); },
        getFields: function(_obj) { return $.extend({}, JSON.parse(JSON.stringify(_obj.config ? _obj.config.fields : {}))); },
        getRowInputByRowId: function(_obj, id) {
            var row = _obj.tbody.find('tr[row-id=' + $.escapeSelector(id) + ']');
            if (row) {
                return this.getRowInput( _obj, row );
            }
            alert('Table-Row with id "' + id + '" not found!');
            return null;
        },
        getRowInput: function(_obj, row) {
            var data = {};
            row.find('td[data-field] :input.myDataTable-td-edit-input').each(function(){
                var fld = $(this).attr('name');
                var val = $(this).val();
                data[fld] = val;
            });
            return data;
        },
        getRowData: function(_obj, row) {
            return $(row).data('row');
        },
        getData: function(_obj) {
            return _obj.data;
        },
        getDataList: function(_obj, key) {
            _obj.thead.find('th datalist[name=' + $.escapeSelect(key) + ']')
        },
        getDataListValues: function(_obj, key) {
            var dl = this.getDataList(key);
            var vals = [];
            dl.find('option').each(function() {
                vals.push( this.value );
            });
            return vals;
        },
        getResult: function(_obj) {
            return _obj.useFilterResult ? _obj.result : [];
        }
    };

    $.fn[moduleName] = function(options) {

        var $args = [].slice.call(arguments);

        if (this.length > 0 && $args.length > 0 && $args[0] in _getters) {
            var _obj = $( this[0] ).data( moduleName );
            return _getters[ $args[0] ].apply( _getters, [ _obj].concat( $args.slice(1) ));
        }

        return this.each(function () {
            var _elm = this;
            var elm = $( _elm );
            var _obj = elm.data(moduleName);

            if ($args.length > 1 ) {
                if ($args[0] === 'setData') {
                    console.log(moduleName + '.' + $args[0] + ' #240', { _elm, elm, $args,_obj });
                }
            }

            var _methods = {
                _init: function() {
                    _obj = elm.data(moduleName) || {};
                    console.log('_init ', '#' +
                        elm.attr('id') +
                        '.' + elm.attr('class').split(' ').join('.')
                    );

                    var defaults = JSON.parse(JSON.stringify($.fn[moduleName].defaults));
                    var _build = {
                        initialized: true,
                        $elm: $( this ),
                        elm,
                        tbl: null,
                        thead: null,
                        tbody: null,
                        tfoot: null,
                        paging: null,
                        config: $.extend({}, defaults, options),
                        data: ('data' in options) ? options.data : [],
                        filter: [],
                        result: [],
                        useFilterResult: false,
                    };
                    elm.toggleClass('sortable', true);

                    _build.tbl = (elm.is("table")) ? elm : $("<table/>").appendTo(elm);
                    _build.thead = _build.tbl.find("thead").length
                            ? _build.tbl.find("thead")
                            : $("<thead/>").appendTo( _build.tbl );
                    _build.hdrow = _build.thead.find("tr").length
                            ? _build.thead.find("tr")
                            : $("<tr/>").appendTo( _build.thead );
                    _build.tbody = _build.tbl.find("tbody").length
                            ? _build.tbl.find("tbody")
                            : $("<tbody/>").appendTo( _build.tbl );
                    _build.tfoot = _build.tbl.find("tfoot").length
                            ? _build.tbl.find("tfoot")
                            : $("<tfoot/>").appendTo( _build.tbl );
                    $.extend(_obj, _build);
                    elm.data(moduleName, _obj);
                    _obj = elm.data(moduleName);

                    if (_obj.config.showAllFields && _obj.config.colNames.length > 0) {
                        for(var ni = 0; ni < _obj.config.colNames.length; ni++) {
                            var n = _obj.config.colNames[ ni ];
                            if (!(n in _obj.config.fields)) {
                                _obj.config.fields[ n ] = { name: n }
                            }
                        }
                    }
                },
                getElm: function() { return _obj.elm; },
                getTable: function() { return _obj.tbl; },
                getThead: function() { return $(_obj.tbl).find('thead'); },
                getTBody: function() { return $(_obj.tbl).find('tbody'); },
                getTotal: function() { return (_obj.useFilterResult) ? _obj.result.length : _obj.data.length; },
                getPageNr: function() { return _obj.config.page; },
                getPageInfo: function() {
                    var numPages = this.getNumPages();
                    var page = _obj.config.page;
                    var pageSize = _obj.config.pagesize;
                    var total = this.getTotal();
                    var offset = 1 + (_obj.config.page - 1) * _obj.config.pagesize;
                    var end = Math.min(total, _obj.config.page * _obj.config.pagesize);
                    var text = offset + " bis " + end + " von " + total + " Einträgen";
                    return {
                        page,
                        offset,
                        end,
                        total,
                        numPages,
                        pageSize,
                        text
                    };
                },
                getNumPages: function() {
                    if (!_obj.config.pagesize) {
                        return 1;
                    }
                    return Math.ceil(this.getTotal() / _obj.config.pagesize);
                },
                getConfig: function() { return $.extend({}, _obj.config); },
                setConfig: function(props) {
                    _obj.config = fitConfig(props, _obj.data);
                    return this;
                },
                setConfigProperty: function(key, val) {
                    if (key === 'page') {
                        _obj.config[key] = fitConfigPage(val, _obj.config.pagesize, this.getTotal() )
                    }
                    else if (key === 'fields') {
                        _obj.config[key] = fitConfigFields( val );
                    }
                    else if (key === 'rownumbers') {
                        _obj.config[key] = !!val;
                    }
                    else _obj.config[key] = val;
                    return this;
                },
                setFieldConfig: function(field, props) {
                    _obj.config.fields[field] = fitConfigField(props);
                    return this;
                },
                gotoPage: function(page) {
                    var total = (_obj.useFilterResult) ? _obj.result.length : _obj.data.length;
                    _obj.config.page = fitConfigPage(page, _obj.config.pagesize, total);
                    this.renderBody();
                    this.renderFoot();
                    return this;
                },
                getDataColIdx(key) {
                    if (_obj.config.colIndex === 'NUM' && _obj.config.colNames.length > 0) {
                        var idx = _obj.config.colNames.indexOf(key);
                        return idx > -1 ? idx : null;
                    }
                    return key;
                },
                deleteRow: function(row) {
                    var rowData = $(row).data('row');
                    return this.deleteRowByData(rowData);
                },
                deleteRowByData: function(rowData) {
                    var key = _obj.config.key;

                    if (key && key in rowData) {
                        this.deleteById( rowData[key]);
                    } else {
                        console.error('Cannot find key ' + key + ' in rowData', { rowData });
                    }
                },
                deleteById: function(id) {
                    var key = _obj.config.key;
                    var idx = this.getDataColIdx(key);
                    if (idx === null) {
                        return console.error('Cannot find key ' + key + ' in colName: ', _obj.config.colNames);
                    }

                    var found = false;
                    var d = _obj.data;
                    for (var i = 0; i < d.length; i++) {
                        if (d[i][idx] == id) {
                            _obj.data.splice(i,1);
                            found = true;
                        }
                    }

                    if (!found) {
                        console.error('Cannot find id ' + id + ' in data');
                        return false;
                    }

                    if (_obj.useFilterResult && _obj.result.length > 0) {
                        var rfound = false;
                        var rd = _obj.data;
                        for (var ri = 0; i < rd.length; i++) {
                            if (rd[ri][idx] == id) {
                                _obj.result.splice(i, 1);
                                rfound = true;
                            }
                        }
                        if (!rfound) {
                            console.log('Cannot find id in result');
                        }
                    }
                    this.renderBody();
                },
                updateById: function(id, row) {
                    var key = _obj.config.key;
                    var rows = _obj.data;
                    console.log('#174 called updateById', { key, id, row, result: _obj.result });
                    for(var i = 0; i < rows.length; i++) {
                        if (key in rows[i] && rows[i][key] === id) {
                            $.extend(rows[i], row);
                        }
                    }
                },
                addRow: function(row, refreshColFilter = true) {
                    if (typeof _obj.config.onInsert === 'function') {
                        var rsp = _obj.config.onInsert(row, _elm);
                        if (rsp === false || rsp === null) {
                            return;
                        }
                        if (rsp && rsp instanceof Promise) {
                            rsp.then(function() {
                               _obj.data.push( row );
                            });
                        } else {
                            _obj.data.push(row);
                        }
                    } else {
                        _obj.data.push(row);
                    }

                    if (_obj.config.colfilters && refreshColFilter) {
                        this.renderColFilterLists();
                    }
                },
                result: [],
                useFilterResult: false,
                orderby: function(field, dir) {
                    var allowMultiSort = _obj.config.allowMultiSort;
                    var isAsc = (arguments.length < 2 || dir.toString().toUpperCase() !== 'DESC');
                    var sortDir = (isAsc) ? 'ASC' : 'DESC';
                    var toggleFaktor = isAsc ? 1 : -1;
                    var fieldName = field;
                    if (_obj.config.colIndex === 'NUM' && _obj.config.colNames.length > 0) {
                        if (_obj.config.colNames.indexOf( field ) < 0) {
                            alert('Unbekanntes Sortierfeld: ' + fieldName + "\nFields: " + JSON.stringify(_obj.config.colNames));
                            return;
                        }
                        field = _obj.config.colNames.indexOf( field );
                    } else if (_obj.data.length && !(field in _obj.data[0])) {
                        alert('Unbekanntes Sortierfeld: ' + fieldName + "\nFields: " + JSON.stringify(Object.keys(_obj.data[0])));
                        return;
                    }

                    if (_obj.data.length && field in _obj.data[0]) {

                        if (!allowMultiSort || _obj.config.multiSortBy.length < 1
                            || (_obj.config.multiSortBy.length === 1 && _obj.config.multiSortBy[0].field === field)) {
                            var cbSort = function(a, b) {
                                var sortCase = '';
                                var re = 0;
                                if (!(field in a) || !(field in b)) {
                                    if (!(field in a) && !(field in b)) {
                                        re = 0;
                                    } else if (!(field in a)) {
                                        re = toggleFaktor * (!(field in a) ? -1 : 1);
                                    }
                                    console.error("Order-Field " + field + " not found", {a, b});
                                    return re;
                                }
                                else if (!isNaN(a[field]) && !isNaN(b[field]) ) {
                                    sortCase = 'Number-Compare';
                                    var av = +a[field], bv = +b[field];
                                    re = (av === bv) ? 0 : (toggleFaktor * (av <= bv ? -1 : 1));
                                }
                                else if (typeof a[field] === 'string' && typeof b[field] === 'string') {
                                    sortCase = 'String-Compare';
                                    var av = a[field].toLowerCase(), bv = b[field].toLowerCase();
                                    re = (av === bv) ? 0 : (toggleFaktor * (av <= bv ? -1 : 1));
                                } else {
                                    sortCase = 'Other-Compare';
                                    var av = a[field], bv = b[field];
                                    re = (av === bv) ? 0 : (toggleFaktor * (av < bv ? -1 : +(av > bv)));
                                }

                                return re;
                            };

                            if (allowMultiSort) {
                                addComparer(_obj, { field, dir: sortDir, name: fieldName, cbSort });
                            }
                            this.sort(cbSort).renderBody();
                        } else {
                            var multiSortBy = _obj.config.multiSortBy;
                            var cbSort = createFncSortCompare(_obj, field, sortDir);

                            addComparer(_obj, { field, dir: sortDir, name: fieldName, cbSort });
                            if (typeof cbSort !== 'function') {
                                console.error("cbSort for " + field + " " + sortDir + " is not a function\n" + (typeof cbSort));
                            }
                            var multiSortInfo = (typeof multiSortBy.map === 'function')
                                ? multiSortBy.map(function(v) { return v.field + " " + v.dir; }).join(', ')
                                : 'UNKNOWN';
                            console.log('cbMultiSort #577', { field, sortDir, fieldName, multiSortBy, multiSortInfo });

                            var errorShownMultiSort = false;
                            var maxLog = 50;
                            var logNr = 0;

                            var cbMuliSort = function(a, b) {
                                var re = 0;
                                var pathA = '';
                                var pathB = '';
                                for(var i = 0; i < multiSortBy.length; i++) {
                                    var f = multiSortBy[i].field;
                                    var d = multiSortBy[i].dir;
                                    if (!errorShownMultiSort &&
                                        (typeof multiSortBy[i] !== 'object'
                                        || !multiSortBy[i].cbSort
                                        || typeof multiSortBy[i].cbSort !== 'function'
                                        )) {
                                        errorShownMultiSort = true;
                                        alert("ERROR in multiSortBy[" + i + "]\n" + JSON.stringify(multiSortBy));
                                    }
                                    if (logNr < maxLog) {
                                        re = multiSortBy[i].cbSort(a, b);
                                        pathA+= "[DIR:" + d + "]/" + f + ':' + a[f] + '(' + re + ')';
                                        pathB+= "/" + f + ':' + b[f];
                                    }
                                    if (re !== 0) break;
                                }
                                if (logNr < maxLog) {
                                    ++logNr;
                                    console.log('cbMultiSort', "#"+logNr, re, 'a', pathA, 'b', pathB);
                                    if (re === 0) {
                                        console.log({re, pathA, a, pathB, b });
                                    }
                                }
                                return re;
                            };
                            this.sort(cbMuliSort).renderBody();
                        }
                    }

                    return this;
                },
                searchByColFilter: function() {
                    var colQueries = {};
                    var filterRow = _obj.thead.find("tr.col-filters");
                    var filterControls = filterRow.find(":input.col-filter-control");
                    var usedControls = filterControls.filter( function() { return $(this).val() !== ''; });
                    var dataIsAssoc = this.dataIsAssoc();

                    usedControls.each(function() {
                        var fld = $(this).closest("th").data( 'field');
                        if (dataIsAssoc) {
                            colQueries[ fld ] = $(this).val().toString().toLowerCase();
                        } else {
                            var colIdx = _obj.config.colNames.indexOf( fld );
                            if (colIdx === -1) {
                                console.error({colName: _obj.config.colNames, fld, colIdx});
                                return;
                            }
                            colQueries[ colIdx ] = $(this).val().toString().toLowerCase();
                        }
                    });
                    console.log('#378 serchByColFilter', { colQueries});

                    var cbFilter = (!Object.keys(colQueries).length) ? null : function(row) {
                        for(var _field in colQueries) {
                            if (!(_field in row)) {
                                if (1) {
                                    console.error( '-- Query-Field Not Found in row ', { _field, row });
                                }
                                return false;
                            }
                            var _val = row[ _field ];

                            if (null === _val || _val === '') {
                                if (1) {
                                    console.debug( '-- Skip empty Field');
                                }
                                return false;
                            }

                            if (!~(_val.toString().toLowerCase().indexOf( colQueries[ _field ]))) {
                                if (1) {
                                    console.log( '-- Not Found ' + colQueries[ _field ] +
                                        " in " + _field + ": " +
                                        _val.toString().toLowerCase());
                                }
                                return false;
                            }

                            if (1) {
                                console.log( '++ Found ' + colQueries[ _field ] + " in " + _field +
                                    ": " + _val.toString().toLowerCase());
                            }
                        }
                        return true;
                    };

                    return this.filter( cbFilter ).renderBody();
                },
                filter: function(cbFilter) {
                    if (!cbFilter || typeof cbFilter !== 'function') {
                        _obj.useFilterResult = false;
                    } else {
                        _obj.result = _obj.data.filter(cbFilter);
                        _obj.useFilterResult = _obj.result.length != _obj.data.length;
                    }
                    _obj.config.page = 1;
                    return this;
                },
                sort: function(cbSort) {
                    if (_obj.useFilterResult) {
                        _obj.result.sort(cbSort);
                    } else {
                        _obj.data.sort( cbSort );
                    }
                    _obj.config.page = 1;
                    return this;
                },
                init: function() {
                    if ( !this.getElm().data('dataTableRendered') ) {
                        this.getElm().data({
                            'dataTableRendered': 1,
                            'dataTableRenderer': this,
                            data: _obj.data
                        });
                        this.render();
                    }
                    return this;
                },
                destroy: function() {
                    var d = $(_elm).data();
                    var aDataProps = [ 'data', moduleName, 'myDataTable', 'dataTableRenderer', 'dataTableRendered' ];
                    for (var i = 0; i < aDataProps.length; i++) {
                        var _pName = aDataProps[i];
                        if (_pName in d) {
                            delete d[_pName];
                        }
                    }
                    $(_elm).html("");
                    return this;
                },
                render: function() {
                    this.renderHead();
                    if (_obj.config.colfilters) {
                        this.renderColFilter();
                    }
                    this.renderBody();
                    this.renderFoot();
                    return this;
                },
                getAssocRow: function(_d) { return _d; },
                dataIsAssoc: function() { return true; },
                renderHead: function() {
                    var $this = this;
                    var colspan = Object.keys(_obj.config.fields).length;
                    if (_obj.config.rownumbers) {
                        colspan+= 1;
                    }
                    if (_obj.config.rowactions) {
                        colspan+= 1;
                    }
                    _obj.hdrow.html('');

                    if (_obj.config.title || (_obj.config.insertable && _obj.config.onOpenInsert)
                        || _obj.config.showColumnPicker || _obj.config.showReloadButton) {
                        console.log('myDataTable.js #501 renderHead', { 'obj.config': _obj.config });

                        var hdTrTitle = _obj.thead.find("tr.title").length
                            ? _obj.thead.find("tr.title")
                            : $("<tr>").addClass("title").prependTo(_obj.thead);

                        var hdThTitle = hdTrTitle.find('th').length
                            ? hdTrTitle.find('th')
                            : $("<th/>").attr("colspan", colspan).appendTo(hdTrTitle);

                        if (_obj.config.title) {
                            hdThTitle.html(_obj.config.title);
                        }

                        if (_obj.config.colFilterDialog) {
                        // <span class="item"><button onclick="editTablTableCols()"
                        // class="ui blue circular sync alternate icon button"><i class="th icon"></i></button></span>
                        }

                        if (_obj.config.showReloadButton && typeof _obj.config.onReload === 'function') {
                            hdThTitle.append(
                                $("<button/>")
                                    .addClass("ui blue button mini sync alternate icon button")
                                    .css("float", "right")
                                    .off('click')
                                    .on('click', function() {
                                        _obj.config.onReload.apply(_elm);
                                    })
                                    .append( $("<i/>").addClass("sync alternate icon"))
                            );
                        }

                        if (_obj.config.showColumnPicker) {
                            hdThTitle.append(
                                $("<button/>")
                                    .addClass("ui blue button mini icon")
                                    .css("float", "right")
                                    .off('click')
                                    .on('click', function() {
                                        $this.showColumsDialog();
                                    })
                                    .append( $("<i/>").addClass("th icon"))
                            );
                        }

                        if (_obj.config.title && _obj.config.onOpenInsert) {
                            hdThTitle.append(
                                $("<button/>")
                                    .addClass("ui icon button mini primary")
                                    .css('float', 'right')
                                    .append($("<i class=\"plus icon\"'/>")
                                        .css({marginLeft:'0.1em', marginRight:'0.1em'})
                                    )
                                    .append(' add ')
                                    .on('click', function(e) {
                                        var cb = _obj.config.onOpenInsert();
                                        if (cb && (cb instanceof Promise) || ('then' in cb && 'catch' in cb)) {
                                            cb.then( function(addRow) {
                                                if (addRow) {
                                                    console.log('myDataTable.js #544 after onOpenInsert addRow', addRow);
                                                    $this.addRow(addRow);
                                                } else {
                                                    console.log('myDataTable.js #547 after onOpenInsert do not addRow');
                                                }
                                            })
                                                .catch(function() {
                                                    console.log('myDataTable.js #551 after onOpenInsert do not addRow');
                                                });
                                        } else {
                                            console.log('myDataTable.js #554 after onOpenInsert. No Promise or callback');
                                        }
                                    })
                            );
                        }
                    }

                    if (_obj.config.rownumbers) {
                        _obj.hdrow.append(
                            $('<th/>').addClass("col-nr").text( "#Nr" )
                        );
                    }
                    if (_obj.config.rowactions) {
                        _obj.hdrow.append(
                            $('<th/>').addClass("col-actions").html( "<i class=\"wrench icon\"></i>" )
                        );
                    }
                    var cf = _obj.config.fields;
                    for (var _col in cf) {
                        if ( cf[ _col].hidden ) {
                            continue;
                        }

                        var th = $("<th/>")
                            .attr('scope', 'col')
                            .attr("data-field", _col)
                            .addClass("col-" + _col)
                            .text( cf[_col].name )
                            .css({cursor: 'pointer'})
                            .on('click', function() {
                                var multiSort = _obj.config.allowMultiSort;
                                if (!multiSort) {
                                    _obj.hdrow.find('.ordered-asc, .ordered-desc')
                                        .not(this)
                                        .removeClass('ordered-asc ordered-desc sorted ascending descending');
                                } else {
                                    var sortPos = $(this).find(".sortPos");
                                    if (!sortPos.length) {
                                        var sortPosNr = _obj.config.multiSortBy.length +1;
                                        sortPos = $("<i/>")
                                            .addClass("sortPos")
                                            // .text(sortPosNr)
                                            .data('sortPosNr', sortPosNr)
                                            .attr('sortposnr', sortPosNr)
                                            .attr("title", "Aus Sortierung entfernen")
                                            .on('mouseover', function() {
                                                $(this).addClass("ui icon times");
                                            })
                                            .on('mouseout', function() {
                                                $(this).removeClass("ui icon times");
                                                // $(this).text( $(this).data('sortPosNr') );
                                            })
                                            .on('click', function(e) {
                                                console.log('sortPos on click #864');
                                                e.preventDefault();
                                                e.stopPropagation();
                                                var multiSort = _obj.config.multiSortBy;
                                                var tr = $(this).closest('tr');
                                                var td = $(this).closest('th');
                                                var selfPos = $(this).attr('sortposnr');
                                                var lastPos = multiSort.length +1;
                                                console.log('sortPos on click #869');

                                                var fld = td
                                                    .removeClass('ordered-asc')
                                                    .removeClass('ordered-desc')
                                                    .removeClass('ascending')
                                                    .removeClass('descending')
                                                    .removeClass('sorted')
                                                    .data('field');
                                                console.log('sortPos on click #878');

                                                _obj.config.multiSortBy = _obj.config.multiSortBy.filter(function(itm) {
                                                    return itm.name !== col;
                                                });
                                                console.log('sortPos on click #883');
                                                multiSort = _obj.config.multiSortBy;
                                                for (var i = 0; i < multiSort.length; i++) {
                                                    tr.find('.col-' + multiSort[i].name)
                                                        .find('.sortPos')
                                                        // .text(i + 1)
                                                        .attr('sortposnr', i + 1)
                                                        .data('sortPosNr', i + 1);
                                                }
                                                console.log('sortPos on click #892');

                                                $(this).remove();
                                                console.log('sortPos on click #895');

                                                if (selfPos < lastPos && multiSort.length > 0) {
                                                    var len = multiSort.length;
                                                    var lastSort = multiSort[len - 1];
                                                    $this.orderby( lastSort.name, lastSort.dir);
                                                }

                                                console.log('sortPos on click #903');
                                            });

                                        $(this).append(sortPos);
                                    }
                                }
                                
                                var col = $(this).data("field");
                                var dir = (
                                    $(this).is('.ordered-asc')
                                    ||
                                    ($(this).is('.ordered-desc') && $(this).data("default-dir") === 'desc')
                                ) ? 'desc' : 'asc';

                                $(this)
                                    .toggleClass('ordered-asc ascending', dir === 'asc')
                                    .toggleClass('ordered-desc descending', dir === 'desc');

                                $(this).addClass('sorted');

                                return $this.orderby( col, dir);
                            })
                            .appendTo( _obj.hdrow );

                    }
                    return this;
                },
                renderColFilter: function() {
                    // return this;
                    var filterRow = _obj.thead.find("tr.col-filters").length
                        ? _obj.thead.find("tr.col-filters")
                        : $("<tr>").addClass("col-filters").appendTo(_obj.thead);

                    filterRow.html('');


                    // <i class="search icon"></i>
                    if (_obj.config.rownumbers) {
                        $("<th/>")
                            .attr('scope', 'row')
                            .append( $("<i/>").addClass("search icon") )
                            .appendTo( filterRow );
                    }
                    if (_obj.config.rowactions) {
                        $("<th/>")
                            .attr('scope', 'row')
                            .appendTo( filterRow );
                    }

                    var cf = _obj.config.fields;
                    for(var _col in cf) {
                        if ( cf[ _col].hidden ) {
                            continue;
                        }

                        var _field = ( ('field' in cf[ _col]) && cf[ _col].field) ? cf[ _col].field : _col;
                        var colName = ( ('name' in cf[ _col]) && cf[ _col].name) ? cf[ _col].name : _col;

                        var th = $("<th/>")
                            .attr({'scope': 'col', "data-field": _field } )
                            .addClass("col-filter col-" + _col)
                            .appendTo( filterRow );

                        var dataListId = 'datalist-' + _col;
                        $("<input/>").attr({name: colName, list: dataListId}).addClass("col-filter-control").appendTo(th)
                            .on('input', this.searchByColFilter.bind(this) );

                        var dataList = $("<datalist/>")
                            .attr({name: _col, id:dataListId})
                            .addClass("col-filter-control").appendTo(th);
                        this.fillDataList(dataList, colName);
                    }
                    return this;
                },
                renderColFilterLists: function() {
                    var cf = _obj.config.fields;
                    for(var _col in cf) {
                        if ( cf[ _col].hidden ) {
                            continue;
                        }
                        var colField = ( ('field' in cf[ _col]) && cf[ _col].field) ? cf[ _col].field : _col;

                        var th = $("th[data-field=" + colField + "].col-filter");
                        var dataListId = 'datalist-' + _col;
                        var dataList = th.find("datalist#" + dataListId);

                        this.fillDataList(dataList, colField);
                    }
                    return this;
                },
                fillDataList: function(dataList, key) {
                    var idx = this.getDataColIdx(key);
                    $(dataList).html('');
                    var d = _obj.data
                        .filter(function(v){ return (idx in v) && v[idx] !== null && v[idx] !== ''; })
                        .map(function(v) { return v[idx]; })
                            .sort(),
                        l = d.length, u = [];

                    var last = '';
                    var count = 0;
                    var opt = null;
                    for(var i = 0; i < l; i++) {
                        if (!d[i]) continue;
                        if (i === 0 || last !== d[i]) {
                            if (opt && count > 1) {
                                opt.attr('label', count + ' Einträge');
                            }
                            count = 1
                            u.push(d[i]);
                            opt = $("<option/>").val( d[i] ).appendTo( dataList );
                            last = d[i];
                            continue;
                        }
                        count++;
                        if (i + 1 === l && opt && count > 1) {
                            opt.attr('label', count + ' Einträge');
                        }

                    }
                },
                setData: function(data) {
                    _obj.data = data;
                    _obj.result = [];
                    if (typeof _obj.thead === 'object' && _obj.thead.find) {
                        var input = _obj.thead.find(':input');
                        if (input && input.trigger) {
                            input.trigger('reset').val('');
                        }
                    } else {
                        console.log(moduleName + '.' + $args[0] + ' #825 _obj.thead.find not found!!!');
                    }
                    this.render();
                },
                setFieldProperty: function(field, propName, propValue, rerender = false) {
                    if (!(field in _obj.config.fields)) return;
                    if (!(propName in _obj.config.fields[field])) return;
                    if (typeof propValue !== typeof _obj.config.fields[field][propName]) return;

                    _obj.config.fields[field][propName] = propValue;
                    if (rerender) {
                        this.renderHead();
                        this.renderBody();
                    }
                },
                showFields: function(fields, rerender = true) {
                    var fieldsWereChanged = false;
                    var fieldsWithChanges = [];
                    for (var i = 0; i < fields.length; i++) {
                        var fld = fields[i];
                        if (fld in _obj.config.fields && _obj.config.fields[fld].hidden) {
                            _obj.config.fields[fld].hidden = false;
                            fieldsWereChanged = true;
                            fieldsWithChanges.push(fld);
                        }
                    }

                    if (!fieldsWereChanged && rerender) {
                        return;
                    }

                    this.render();
                },
                hideFields: function(fields, rerender = true) {
                    var fieldsWereChanged = false;
                    var fieldsWithChanges = [];
                    for (var i = 0; i < fields.length; i++) {
                        var fld = fields[i];
                        if (fld in _obj.config.fields && !_obj.config.fields[fld].hidden) {
                            _obj.config.fields[fld].hidden = true;
                            fieldsWereChanged = true;
                            fieldsWithChanges.push(fld);
                        }
                    }

                    if (!fieldsWereChanged && rerender) {
                        return;
                    }

                    this.render();
                },
                renderBody: function() {
                    var offset = (_obj.config.page - 1) * _obj.config.pagesize;
                    var dataSource = _obj.useFilterResult ? _obj.result : _obj.data;
                    
                    var end = _obj.config.pagesize > 0 
                        ? Math.min(dataSource.length, offset + _obj.config.pagesize) 
                        : dataSource.length;


                    _obj.tbody.html('');

                    _obj.config.idx = this.getDataColIdx(_obj.config.key);
                    if (_obj.config.colIndex === 'NUM' && _obj.config.colNames.length > 0) {
                        var cn = _obj.config.colNames;
                        var cl = cn.length;
                        this.getAssocRow = function(_d) {
                            var row = {};
                            for(var i = 0; i < cl; i++) {
                                row[ cn[i]] = _d[i];
                            }
                            return row;
                        };
                        this.dataIsAssoc = function() { return false; };
                    } else {
                        this.getAssocRow = function(_d) { return _d; };
                        this.dataIsAssoc = function() { return true; };
                    }

                    for(var tri = offset; tri < end; tri++ ) {

                        var _d = this.getAssocRow(dataSource[ tri ]);

                        this.addRenderRow( _d, tri );

                    }

                    if (_obj.config.onRendered && typeof _obj.config.onRendered === 'function') {
                        _obj.config.onRendered.apply(_obj.tbl, []);
                    }
                    this.renderPaging();
                    return this;
                },
                addRenderRow: function (rowData, tri) {
                    var self = this;
                    var cf = _obj.config.fields;
                    var _d = rowData;
                    var row = $("<tr/>").data("row", _d).appendTo( _obj.tbody );

                    if (_obj.config.key) {
                        row.attr("data-rowid", _d[_obj.config.key]);
                    }

                    if (_obj.config.rownumbers) {
                        if (!tri) {
                            tri = row.prev('tr').find('th:first').text() || 0;
                        }
                        $("<th/>").attr('scope', 'row').text( tri + 1 ).appendTo( row );
                    }

                    if (_obj.config.rowactions) {
                        var tdAct = $("<td/>").addClass("actions").appendTo( row );
                        var btns = $("<div/>").addClass("ui buttons").appendTo( tdAct );

                        if (_obj.config.openDoc && _obj.config.onOpenDoc) {
                            $("<button/>")
                                .addClass('mini ui circular default button icon btn-edit')
                                .append(
                                    $('<i/>').addClass('file icon')
                                )
                                .on( 'click', function() {
                                    var row = $(this).closest('tr');
                                    var rowData = _methods.getRowData(row);
                                    _obj.config.onOpenDoc(row, rowData);
                                })
                                .appendTo( btns );
                        }
                        if (_obj.config.editable) {
                            $("<button/>")
                                .addClass('mini ui circular blue button icon btn-edit')
                                .append(
                                    $('<i/>').addClass('edit icon')
                                )
                                .on( 'click', function() {
                                    var prnt = $(this).closest('.ui.buttons');
                                    var row = $(this).closest('tr');
                                    prnt.find('.btn-edit,.btn-delete').addClass('d-none');
                                    prnt.find('.btn-save,.btn-abort').removeClass('d-none');
                                    _methods.rowEdit( row );
                                })
                                .appendTo( btns );

                            $("<button/>")
                                .addClass('mini ui circular grey button icon btn-abort d-none')
                                .append(
                                    $('<i/>').addClass('minus circle icon')
                                )
                                .on( 'click', function() {
                                    var row = $(this).closest('tr');
                                    _methods.rowUnedit(row);
                                    var prnt = $(this).closest('.ui.buttons');
                                    prnt.find('.btn-save,.btn-abort').addClass('d-none');
                                    prnt.find('.btn-edit,.btn-delete').removeClass('d-none');
                                })
                                .appendTo( btns );

                            $("<button/>")
                                .addClass('mini ui circular green button icon btn-save d-none')
                                .append(
                                    $('<i/>').addClass('save icon')
                                )
                                .on( 'click', function() {
                                    var row = $(this).closest('tr');
                                    _methods.rowEdit( row );
                                    var prnt = $(this).closest('.ui.buttons');
                                    var ok = true;
                                    var wait = false;

                                    var done = function(data = null) {
                                        if (data) {
                                            console.log('#804 mDT update success done. saveRowInput', { data });
                                            _methods.saveRowInput(row, data);
                                        }
                                        _methods.rowUnedit(row);
                                        prnt.find('.btn-save,.btn-abort').addClass('d-none');
                                        prnt.find('.btn-edit,.btn-delete').removeClass('d-none');
                                    };
                                    if (typeof _obj.config.onSave === 'function') {
                                        var inputData = _methods.getRowInput(row);
                                        var inputControls = _methods.getRowInputControls(row);
                                        var rowData = _methods.getRowData(row);
                                        var updated = _obj.config.onSave(inputData, rowData, inputControls, row);

                                        if (!isUsableAsPromise(updated) && isConvertableToPromiseWithThenCatchFinally(updated)) {
                                            makeUsableAsPromise(updated);
                                        }

                                        if (isPromise(updated) || isUsableAsPromise(updated)) {
                                            $(row).waitMe('show');
                                            console.log('#794 mDT.addRenderRow wait for Server-Process');
                                            wait = true;
                                            updated
                                                .then( function(response) {
                                                    console.log('#822 mDT updated.then response', { response  });
                                                    done(response);
                                                })
                                                .catch( function() {
                                                    console.log('#801 mDT.addRenderRow catch updated is instanceof Promise', { arguments }, 'row', row);
                                                    done();
                                                })
                                                .finally(function() {
                                                    $(row).waitMe('hide');
                                                })
                                            ;
                                        }
                                        else if (updated === false) {
                                            ok = false;
                                        }
                                        else {
                                            console.log('#821 mDT.addRenderRow call saveRowInput', { updated, row });
                                        }
                                    }

                                    if (!wait && ok) {
                                        console.log('#826 mDT.addRenderRow myDataTable.js done');
                                        done({});
                                    }
                                })
                                .appendTo( btns );
                        }

                        if (_obj.config.deletable) {
                            $("<button/>")
                                .addClass('mini ui circular red button icon btn-delete')
                                .append(
                                    $('<i/>').addClass('trash alternate outline icon')
                                ).on( 'click', function() {
                                var row = $(this).closest('tr');
                                var removed = false;
                                row.addClass('myDataTable-selected-for-delete');
                                console.log('#1037 mDT.addRenderRow delete on click', { 'this': this, rowClass: row.attr('class'), rowHtml: row.html() });
                                if (typeof _obj.config.onDelete === 'function') {
                                    var rowData = self.getRowData(row);
                                    setTimeout(function() {

                                        var delResult = _obj.config.onDelete(rowData, row);
                                        if (delResult instanceof Promise) {
                                            delResult.then( function() {
                                                _methods.deleteRowByData( rowData );
                                                row.remove();
                                                removed = true;
                                            }).finally(function() {
                                                if (!removed) {
                                                    row.removeClass('myDataTable-selected-for-delete');
                                                }
                                            });
                                            return false;
                                        } else if (delResult === true) {
                                            _methods.deleteRowByData( rowData );
                                            row.remove();
                                            removed = true;
                                            return true;
                                        } else {
                                            if (!removed) {
                                                row.removeClass('myDataTable-selected-for-delete');
                                            }
                                        }
                                    }, 75);
                                }
                            })
                                .appendTo( btns );
                        }
                    }

                    var flist = [];

                    for(var _col in cf) {
                        var _c = cf[ _col ];

                        if (_c.hidden || _c.colspan === 0) {
                            continue;
                        }

                        var csp = _c.colspan;
                        var val = _col in _d ? _d[ _col ] : null;

                        var cell = $("<td/>").attr("data-field", _col)
                            .addClass("col-" + _col).text( val ).appendTo( row );

                        if (csp > 1) {
                            cell.attr( 'colspan', csp);
                        }

                        if (_c.formatter) {
                            flist.push(
                                this.prepareCellFormatter(_c.formatter, cell, val, _col, row, _d)
                            );
                        }

                    }
                    for(var fli = 0; fli < flist.length; fli++) {
                        flist[ fli ]();
                    }
                    if (_obj.config.rowformatter) {
                        _obj.config.rowformatter.call(row, _d);
                    }

                    return row;
                },
                prepareCellFormatter: function(formatter, cell, val, col, row, d) {
                    if (formatter in $.fn.myDataTable.formatters) {
                        var args = [].slice.apply(arguments, [2]);
                        return function() {
                            var s = $.fn.myDataTable.formatters[formatter].apply(cell, args);
                            s !== null && cell.html(s);
                        }
                    }
                    else if (typeof formatter === 'function') {
                        return formatter.bind( cell, val, col, row, d);
                    } else return function() {
                        val !== null && cell.html( val );
                    }
                },
                renderFoot: function() {
                    if (('showFooter' in _obj.config) && !_obj.config.showFooter) {
                        return;
                    }

                    _obj.tfoot = _obj.tbl.find("tfoot").length
                        ? _obj.tbl.find("tfoot")
                        : $("<tfoot/>").appendTo( _obj.tbl );

                    // var tr = _obj.tfoot.find("tr").length
                    //     ? _obj.tfoot.find("tr")
                    //     : $("<tr/>").appendTo( _obj.tfoot );
                    //
                    // var th = $("<th/>");
                    var offset = 0;
                    var numFooterCells = _obj.config.footer.length;
                    var numCols = (_obj.config.rownumbers ? 1 : 0) + Object.keys(_obj.config.fields).length;

                    if (_obj.config.rownumbers) {
                        numCols += 1;
                    }

                    if (_obj.config.rowactions) {
                        numCols += 1;
                    }

                    _obj.config.footer.forEach( function(ftCnf, i) {
                        var tr = $("<tr/>").appendTo( _obj.tfoot );
                        var th = $("<th/>");
                        var cellOffset = ftCnf.offset || offset;
                        var cellSpan = ('colspan' in ftCnf) ? ftCnf.colspan : 0;

                        if (cellOffset > offset) {
                            var fillTh = $("<th/>").appendTo( tr );
                            if (cellOffset - offset > 1) {
                                fillTh.attr("colspan", cellOffset - offset);
                            }
                        }
                        if (cellSpan > 1) {
                            th.attr("colspan", cellSpan);
                        } else if (cellSpan === 0 && (i + 1) === numFooterCells) {
                            cellSpan = numCols - offset;
                            th.attr("colspan", numCols - offset);
                        } else {
                            cellSpan = 1;
                        }
                        offset = cellOffset + cellSpan;
                        th.appendTo( tr );
                        console.log('#1025 renderFoot ', { ftCnf });
                        if ('formatter' in ftCnf && typeof ftCnf.formatter === 'function') {
                            console.log('#1027 renderFoot ');
                            ftCnf.formatter.apply( th, [_obj.config.fields]);
                        } else {
                            console.log('#1030 renderFoot ');
                        }
                    })
                },
                renderPaging: function() {
                    var self = this;
                    var numPages = this.getNumPages();
                    console.log('#1063 renderPaging() ', {
                        numPages,
                        'this.getTotal()': this.getTotal(),
                        '_obj.config.pagesize': _obj.config.pagesize
                    });
                    if (numPages < 2) {
                        return this;
                    }
                    if (_obj.config.showPaging && !_obj.paging) {
                        var colspan = Object.keys(_obj.config.fields).length + (_obj.config.rownumbers ? 1 : 0);
                        if (_obj.config.rowactions) {
                            colspan += 1;
                        }
                        var tr = $('<tr/>').appendTo( _obj.tfoot );
                        var th = $('<th/>').attr('colspan', colspan).appendTo( tr );
                        _obj.paging = $('<div/>').addClass('table-paging').appendTo( th );
                    }
                    _obj.paging.html('');
                    _obj.btnPages = $('<div/>').addClass('ui buttons').appendTo(_obj.paging);
                    if (!_obj.config.showPaging) {
                        return this;
                    }

                    var numBtns = Math.min(6, numPages);
                    var start = Math.max(1, Math.ceil(_obj.config.page  - (numBtns/2)));
                    var end = start + numBtns;
                    if (start + numBtns > numPages) {
                        start = Math.max(1, numPages - numBtns);
                        end = numPages;
                    }

                    for(var i = start; i <= end; i++) {
                        var _btn = $('<button/>')
                            .attr('data-page', i)
                            .addClass('ui basic button')
                            .text(i)
                            .on('click', function() {
                                var p = $(this).attr('data-page');
                                self.gotoPage(parseInt(p, 10));
                            });
                        if (i === _obj.config.page) {
                            _btn.addClass('active');
                        }
                        _obj.btnPages.append( _btn );
                    }

                    var pgInfo = this.getPageInfo();
                    var _btn = $('<span/>')
                        .addClass('ui basic').css({marginLeft: "1rem" })
                        .text( pgInfo.text  );
                    _obj.paging.append( _btn );
                },
                rowEditByRowId: function(id) {
                    var row = _obj.tbody.find('tr[row-id=' + $.escapeSelector(id) + ']');
                    if (row) {
                        this.rowEdit( row );
                        return true;
                    }
                    alert('Table-Row with id "' + id + '" not found!');
                    return false;
                },
                rowEdit: function(row) {
                    var dbg = false;
                    if (!row) {
                        alert('Table-Row is empty!');
                        return false;
                    }
                    var cf = _obj.config.fields;
                    var _d = $(row).data('row');
                    // console.log('#648 rowEdit', { row, fields: cf, data: _d });
                    for(var _col in cf) {
                        var c = cf[_col];
                        if (dbg) {
                            console.log('#653 rowEdit check field ', _col);
                        }
                        var td = $(row).find('td[data-field=' + _col + ']');
                        if (!td.length) {
                            if (dbg) {
                                console.log('#653 rowEdit td not found', _col);
                            }
                            continue;
                        }
                        if (!'editable' in c && 'editor' in c && c.editor) {
                            c.editable = true;
                        }
                        if (!'editable' in c) {
                            c.editable = false;
                            if (dbg) {
                                console.log('#658 rowEdit field is not editable', _col);
                            }
                            continue;
                        }
                        if (!c.editable) {
                            if (dbg) {
                                console.log('#662 rowEdit field is not editable', _col);
                            }
                            continue;
                        }
                        var val = _col in _d ? _d[ _col ] : '';
                        var type = 'type' in c ? c.type : 'text';
                        if (dbg) {
                            console.log('#666 rowEdit field-' +  _col + '-val', val, _col);
                        }
                        var childNodes = [].slice.apply(td[0].childNodes, [0]);
                        var wrapperHide = td.find(".myDataTable-td-hide-wrapper");
                        var inputWrapper = td.find(".myDataTable-td-edit-wrapper");
                        var input = null;
                        td.addClass('myDataTable-td-editmode ui form');

                        if (!wrapperHide.length) {
                            wrapperHide = $("<div/>")
                                .addClass("myDataTable-td-hide-wrapper")
                                .css({display: 'none'}).appendTo(td)
                                .append(childNodes);
                        }

                        if (!inputWrapper.length) {
                            inputWrapper = $("<div/>")
                                .addClass("myDataTable-td-edit-wrapper")
                                .appendTo(td);

                            if (c.editor) {
                                c.editor.apply( inputWrapper, [ val, _col, _d ]);
                            } else {
                                input = $("<input/>")
                                    .attr({ type, name: _col})
                                    .addClass('myDataTable-td-edit-input')
                                    .css({width: "100%"})
                                    .val(val).appendTo( inputWrapper );
                            }

                        } else if (input) {
                            input.val( val );
                        }

                        if (dbg) {
                            console.log('#692 field-' + _col + '-val', {
                                val,
                                childNodes,
                                wrapperHide,
                                inputWrapper,
                                input
                            });
                        }
                    }
                    return row;
                },
                rowUneditByRowId: function(id) {
                    var row = _obj.tbody.find('tr[row-id=' + $.escapeSelector(id) + ']');
                    if (row) {
                        return this.rowUnedit( row );
                    }
                    alert('Table-Row with id "' + id + '" not found!');
                    return null;
                },
                rowUnedit: function(row) {
                    console.log('myDataTable.js rowUnedit #1553: ', { row, rowLength: row.length });
                    row.find('td[data-field]>div.myDataTable-td-hide-wrapper').each(function(){
                        var td = $(this).closest('td');
                        td.removeClass('myDataTable-td-editmode ui form');
                        var wrapperHide = this;
                        var childNodes = [].slice.apply(wrapperHide.childNodes, [0]);
                        var inputWrapper = td.find('.myDataTable-td-edit-wrapper');
                        inputWrapper.remove();
                        $(childNodes).insertBefore( wrapperHide );
                        wrapperHide.remove();
                    });
                    return row;
                },
                getRowInputByRowId: function(id) {
                    var row = _obj.tbody.find('tr[row-id=' + $.escapeSelector(id) + ']');
                    if (row) {
                        return this.getRowInput( row );
                    }
                    alert('Table-Row with id "' + id + '" not found!');
                    return null;
                },
                getRowInput: function(row) {
                    var data = {};
                    row.find('td[data-field] :input.myDataTable-td-edit-input').each(function(){
                        var fld = $(this).attr('name');
                        var val = $(this).val();
                        data[fld] = val;
                    });
                    return data;
                },
                getRowInputControls: function(row) {
                    var controls = {};
                    row.find('td[data-field] :input.myDataTable-td-edit-input').each(function(){
                        var fld = $(this).attr('name');
                        if (controls[fld]) {
                            if (!Array.isArray(controls[fld]) ) {
                                controls[fld] = [ controls[fld] ];
                            }
                            controls[fld].push( this );
                        } else {
                            controls[fld] = this;
                        }
                    });
                    return controls;
                },
                getRowData: function(row) {
                    return $(row).data('row');
                },
                setRowData: function(row, data) {
                    return $(row).data('row', data);
                },
                updateRowData: function(row, data) {
                    var rowData = $(row).data('row');
                    for (var k in data) {
                        if (!data.hasOwnProperty(k)) {
                            return false;
                        }
                        rowData[ k ] = data[ k ];
                    }
                    $(row).data('row', rowData);
                },
                saveRowInput: function(row, data) {
                    var rowData = $(row).data('row');
                    var inputData = this.getRowInput(row);
                    var inputControls = this.getRowInputControls(row);

                    for (var k in inputData) {
                        if (!inputData.hasOwnProperty(k)) {
                            continue;
                        }
                        rowData[ k ] = inputData[ k ];
                    }

                    if (data) {
                        for(var k2 of Object.keys(data)) {
                            if (k2 in rowData) {
                                rowData[ k2 ] = data[ k2 ];
                            }
                        }
                    }

                    $(row).data('row', rowData);
                    var key = _obj.config.key || null;
                    if (key && key in rowData) {
                        console.log('#1228 mDT.saveRowInput rowData', { rowData });
                        var rows = _obj.data;
                        for (var i = 0; i < rows.length; i++) {
                            if (rows[i][key] === rowData[key]) {
                                Object.assign(rows[i], rowData);
                                console.log('#1233 mDT.saveRowInput rowData', { i, rowData });
                            }
                        }
                        if (_obj.useFilterResult && _obj.result.length > 0) {
                            console.log('#1237 mDT.saveRowInput rowData', { rowData });
                            var result = _obj.result;
                            for (var i = 0; i < result.length; i++) {
                                if (result[i][key] === rowData[key]) {
                                    Object.assign(result[i], rowData);
                                    console.log('#1242 mDT.saveRowInput rowData', { rowData });
                                }
                            }
                        }
                    } else {
                        console.log('#1247 mDT.saveRowInput cannot assign changes!');
                    }
                    var cf = _obj.config.fields;
                    for (var _col in cf) {
                        if (!cf.hasOwnProperty(_col)) continue;
                        var _f = cf[ _col];
                        if (_f.editable) {
                            var td = $(row).find('td.col-' + $.escapeSelector(_col));
                            td.removeClass('myDataTable-td-editmode ui form');
                            if (!td) continue;

                            if ( _f.formatter) {
                                this.prepareCellFormatter(
                                    _f.formatter, td.html(''), rowData[ _col ], _col, row, rowData
                                )();
                            } else {
                                td.html( rowData[ _col ] );
                            }
                        }
                    }
                    return true;
                },
                showColumsDialog: function() {
                    var modal = $('#myDataTableColConfigDialog');
                    if (modal.length) {
                        modal.remove();
                    }
                    {
                        modal = $("<div/>")
                            .addClass('ui mini modal')
                            .attr('id', "myDataTableColConfigDialog")
                            .append(
                                $("<div/>")
                                    .addClass("header")
                                    .text("Tabellenfelder")
                                    .append(
                                        $("<div/>").addClass("ui mini input")
                                            .append(
                                                $("<input/>").attr({
                                                    type: 'text',
                                                    name: 'filter',
                                                    placeholder: 'Suche nach Feldname...'
                                                })
                                            )
                                    )
                                )
                            .append(
                                $("<div/>")
                                    .addClass("scrolling content")
                                    .css("minHeight", "calc(70vh - 10rem)")
                            )
                            .append(
                                $("<div/>").addClass("actions")
                                    .append( $("<div/>").addClass("ui approve button").text('Übernehmen') )
                                    .append( $("<div/>").addClass("ui cancel button").text('Abbrechen') )
                            );
                    }
                    $('#tableColConfig')
                        .find('input[name=filter]')
                        .off('input')
                        .on("input", function() {
                        var term = $(this).val().toString().toLowerCase();
                        if (!term) {
                            modal.find(".content div.checkbox").show();
                            return;
                        }
                        modal.find(".content div.checkbox").each( function() {
                            var colName = $(this).attr("data-col").toString().toLowerCase();
                            $(this).toggle(colName.indexOf(term) !== -1);
                        });
                    });

                    //function editTablTableCols() {
                        var activeTbl =  $(_obj.elm);
                        var tblFields =_getters.getFields(_obj);

                        var header = modal.find('.header');
                        var content = modal.find('.content');

                        modal.find('input[name=filter]').val('');

                        for(var fld in tblFields) {
                            if (!tblFields.hasOwnProperty(fld)) continue;

                            var visible = !('hidden' in tblFields[fld]) || !tblFields[fld].hidden;
                            var name = tblFields[fld].name;
                            $("<div/>")
                                .addClass("ui checkbox col-" + name)
                                .css({display: 'block', borderBottom: "1px solid rgba(34,36,38,0.15)", padding:"0.3rem 0"})
                                .attr('data-col', name)
                                .append( $("<input/>").attr({type:"checkbox", name: fld}).data('oldValIsVisible', visible).prop('checked', visible) )
                                .append( $("<label/>").text(name) )
                                .appendTo( content );
                        }
                        modal.modal({
                            closeable: true,
                            onDeny    : function(){
                                console.log('OK!');
                                return true;
                            },
                            onApprove : function() {
                                var aShowFields = [];
                                var aHideFields = [];
                                var numVisibleFields = 0;
                                content.find(':checkbox').each(function() {
                                    var oldValIsVisible = $(this).data('oldValIsVisible');
                                    if (this.checked) {
                                        numVisibleFields++;
                                        if (!oldValIsVisible) aShowFields.push(this.name);
                                    } else {
                                        if (oldValIsVisible) aHideFields.push(this.name);
                                    }
                                });
                                if (!numVisibleFields) {
                                    alert('Änderungen werden nicht übernommen. Mindestens ein Feld muss sichtbar sein!');
                                    return;
                                }
                                if (!activeTbl || !activeTbl.length || !activeTbl.is("table")) {
                                    console.error('activeTbl is not valid!');
                                    return;
                                }
                                console.log({activeTbl});

                                if (aShowFields.length) {
                                    activeTbl.myDataTable('showFields', aShowFields);
                                }
                                if (aHideFields.length) {
                                    activeTbl.myDataTable('hideFields', aHideFields);
                                }
                            }
                        }).modal('show');
                    // }
                    // END OF editTab1TableCols
                }
            };

            if ($args.length === 1 && $args[0] === 'destroy') {
                return _methods.destroy.apply(_methods, $args.slice(1));
            }

            if (!_obj || !_obj.initialized) {
                _methods._init();
                _methods.setConfig(_obj.config).init();

                if (typeof _obj.config.onLoad === 'function') {
                    _obj.config.onLoad.call( _elm );
                }
            } else if ($args.length > 0) {
                var len = $args.length;
                var a0 = $args[0];
                var t0 = typeof(a0);

                if ( t0 === 'object') {
                    $.extend(_obj.config, a0);
                } else if (t0 === 'string' && a0.substr(0,1) !== '_') {
                    var param = a0.split('-').slice(1).join('-');
                    if (a0 in _methods) {
                        var re = _methods[a0].apply(_methods, $args.slice(1));
                        if (a0.substr(0, 3) === 'get') {
                            return re;
                        }
                    } else if (a0.substr(0, 4) === 'get-') {
                        if (param in _obj.config) {
                            return _obj.config[param];
                        }
                        if (param in _obj) {
                            return _obj[param];
                        }
                    } else if (a0.substr(0, 4) === 'set-' && len > 1) {
                        if (param in _obj.config) {
                            _obj.config[param] = $args[1];
                        }
                    }
                }
                _methods.render();
            }

            return this;
        });
    };

    $.fn.myDataTable.defaults = {
        // Defaults
        key: 'id',
        params: {},
        rownumbers: true,
        colfilters: true,
        colIndex: 'ASSOC',
        colNames: [],
        title: '',
        showFooter: false,
        showPaging: true,
        showColumnPicker: true,
        showReloadButton: true,
        pagesize: 0,
        showAllFields: false,
        fields: {}, // { name: string, colspan?: number, hidden?: boolean, formatter?: function, colfilter: boolean }
        footer: [],
        data: [],
        dataUrl: '',
        allowMultiSort: true,
        multiSortBy: [],
        editable: false,
        insertable: false,
        deletable: false,
        openDoc: false,
        onLoad: null, // callback()
        onEdit: null, // callback(row, rowData)
        onSave: null,
        onInsert: null, // callback(rowData)
        onDelete: null,  // callback(row, rowData)
        onOpenInsert: null, // callback()
        onOpenDoc: null, // callback(row, rowData)
        onReload: null
    };
    $.fn[moduleName].fieldDefaults = {
        colspan: 1,
        hidden: false,
        formatter: null,
        colfilter: true,
        editable: false,
        type: 'text',
        editor: null,
    };
    $.fn[moduleName].formatters = {
        'date-dmy': function(val) {
            if (val) {
                if (typeof val === 'object' && val.constructor.name === 'Date') {
                    val = val.toISOString();
                }
                if (typeof val === 'string') {
                    val = val.substr(0, 10).split('-').reverse().join('.');
                }
            }
            return val;
        }
    };
    $.fn[moduleName].lib = {
        getInputAsFormDataBySelector: function(selector) {
            var myForm = $("<form/>");
            $(selector).find(':input').each( function(i, o) {
                myForm.append( $(o).clone() );
            });

            var formdata = new FormData( myForm.get(0) );
            // for(var pair of formdata.entries()) { console.log({ pair }); }

            return formdata;
        },
        getInputAsJsonBySelector: function(selector) {

            var getFileData = function(file, finishedCallback) {
                var fileInput = {
                    objectType: 'File',
                    type: file.type,
                    name: file.name,
                    size: file.size,
                    lastModified: v.lastModified,
                    data: null
                };
                data[ k ] = fileInput;

                var rd = new FileReader();
                rd.onloadend = function(e) {
                    fileInput.data = rd.result;
                    finishedCallback(true, 'loadend');
                };
                rd.onabort = function(e) {
                    finishedCallback(false, 'abort');
                };
                rd.onerror = function(e) {
                    finishedCallback(false, 'error');
                };
                rd.readAsDataURL( file );

                return fileInput;
            };

            return new Promise(function(resolve, reject) {
                var myForm = $("<form/>");
                $(selector).find(':input').each( function(i, o) {
                    myForm.append( $(o).clone() );
                });

                var formdata = new FormData( myForm.get(0) );
                var formdataLength = 0;
                for(var k of formdata.keys() ) { formdataLength += 1; }

                var data = {};
                var finished = 0;
                var errors = 0;
                var wait = false;

                for( var input of formdata.entries() ) {
                    var k = input[0];
                    var v = null;
                    var asArray = false;

                    if (v instanceof File) {
                        wait = true;
                        v = getFileData( input[1], function finishedCallback(success, type) {
                            finished++;
                            if (type === 'error') {
                                reject('Cannot load filedata of file-upload-control ' + k);
                                errors++;
                            }

                            if (finished === formdataLength) {
                                resolve( data );
                            }
                        });
                    } else {
                        v = input[1];
                        finished++;
                    }

                    if (k.substr( k.length - 2 ) === '[]') {
                        asArray = true;
                        k = k.substr(0, k.length - 2 );
                    }

                    if (!(k in data)) {
                        data[ k ] = !asArray ? v : [ v ];
                    } else {
                        data[ k ] = [ data[k], v ];
                    }
                }

                if (formdataLength === finished || !wait) {
                    resolve( data );
                }
            });
        },
        postFormData: function(url, formData, getJqXhr, uploadProgressCallback) {
            console.log('#1404 mDT.postFormData', { url, formData, getJqXhr, uploadProgressCallback });
            return new Promise(function(resolve, reject)
            {
                try {
                    console.log('#1408 mDT.postFormData');
                    var jqXhr = jQuery
                        .ajax({
                            url: url,
                            type: "POST",
                            processData: false,
                            contentType: false,
                            data: formData,
                            xhr: function() {
                                console.log('#1416 mDT.postFormData');
                                var innerXHR = new window.XMLHttpRequest();
                                if (uploadProgressCallback) {
                                    innerXHR.upload.addEventListener(
                                        "progress", uploadProgressCallback
                                    );
                                }
                                return innerXHR;
                            },
                            success: function (respData, textStatus, jqXHR) {
                                console.log(
                                    '#1420 mDT.postFormDataT success',
                                    { respData, textStatus, jqXHR });

                                if (respData.success) {
                                    console.log('#1424 mDT.postFormDataT success resolve()');
                                    resolve(respData.data || respData, respData);
                                } else {
                                    console.error('#1427 mDT.postFormDataT success reject()', { respData });
                                    reject(respData.message || respData);
                                }
                            },
                            error: function() {
                                console.log('#1434 mDT.postFormData error', { arguments });
                                reject('#1439 mDT.postFormData Serverfehler', { arguments });
                            }
                        })
                    ;

                    if (getJqXhr) {
                        console.log('#1443 mDT.postFormData getJqXhr');
                        getJqXhr(jqXhr);
                    }
                    console.log('#1446 mDT.postFormData');
                } catch (e) {
                    console.log('#1448 mDT.postFormData catch(e)', { e });
                    reject(e);
                }
            })
        }
    }

}(jQuery));
