

function dataTable( selector, data, props) {

    if (arguments.length === 1) {
        if ( typeof $(selector).data("dataTableRenderer") === 'object' && 'render' in $(selector).data("dataTableRenderer") ) {
            return $(selector).data("dataTableRenderer");
        }
    }

    return (function(selector, data, props){

        var elm = $( selector );
        var tbl = (elm.is("table")) ? elm : $("<table/>").appendTo(elm);
        var thead = tbl.find("thead").length ? tbl.find("thead") : $("<thead/>").appendTo( tbl );
        var hdrow = thead.find("tr").length ? thead.find("tr") : $("<tr/>").appendTo( thead );
        var tbody = tbl.find("tbody").length ? tbl.find("tbody") : $("<tbody/>").appendTo( tbl );
        var config = $.extend({}, props);

        function fitConfigPage(page, pagesize, total) {
            var pageNr = 1;
            if (!isNaN(+page) && pagesize > 0 && total > 0) {
                pageNr = Math.max(1, +page);

                if (pageNr > 1) {
                    pageNr = Math.min( Math.ceil( total / pagesize) );
                }
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
                return { name: field, colspan: 1, hidden: false, formatter: null, colfilter: true };
            }

            if (typeof field !== 'object') {
                return { name: null, colspan:  0, hidden: true };
            }

            var cnfField = $.extend({}, field);

            if (!("name" in cnfField)) {
                cnfField.name = col;
            }

            if (!("colspan" in cnfField)) {
                cnfField.colspan = 1;
            }

            if (!("hidden" in cnfField)) {
                cnfField.hidden = false;
            }

            if (!("formatter" in cnfField) || typeof cnfField.formatter !== 'function') {
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

        var renderer = {
            getSelector: function() { return selector; },
            getElm: function() { return elm; },
            getTable: function() { return tbl; },
            getThead: function() { return $(tbl).find('thead'); },
            getTBody: function() { return $(tbl).find('tbody'); },
            getTotal: function() { return (this.useFilterResult) ? _obj.result.length : _obj.data.length; },
            getPageNr: function() { return config.page; },
            getConfig: function() { return $.extend({}, this.config); },
            setConfig: function(props) {
                config = fitConfig(props, data);
                return this;
            },
            setConfigProperty: function(key, val) {
                if (key === 'page') {
                    config[key] = fitConfigPage(val, config.pagesize, this.getTotal() )
                }
                else if (key === 'fields') {
                    config[key] = fitConfigFields( val );
                }
                else if (key === 'rownumbers') {
                    config[key] = !!val;
                }
                else config[key] = val;
                return this;
            },
            setFieldConfig: function(field, props) {
                config.fields[field] = fitConfigField(props);
                return this;
            },
            gotoPage: function(page) {
                var total = (this.useFilterResult) ? _obj.result.length : _obj.data.length;
                config.page = fitConfigPage(page, config.pagesize, total);
                this.renderBody();
                this.renderFoot();
                return this;
            },
            deleteById: function(id) {
                var key = config.key;
                console.log('#162 called deleteById', { key, id, data, result: this.result });
                if (data.find(function(row) { return row[key] === id})) {
                    data = data.filter(function (row) {
                        return row[key] !== id;
                    });
                } else {
                    console.error('Cannot find id in data');
                    return;
                }
                if (this.result.find(function(row) { return row[key] === id})) {
                    this.result = this.result.filter(function (row) {
                        return row[key] !== id;
                    });
                } else {
                    console.log('Cannot find id in result');
                }
                this.renderBody();
            },
            result: [],
            useFilterResult: false,
            orderby: function(field, dir) {
                var isAsc = (arguments.length < 2 || dir.toString().toUpperCase() !== 'DESC');
                var toggleFaktor = isAsc ? 1 : -1;
                if (data.length && field in data[0]) {
                    var cbSort = function(a, b) {
                        var sortCase = '';
                        if (!field in a || !field in b) {
                            var re = 0;
                            console.error("Order-Field " + field + " not found", {a, b});
                            return re;
                        }
                        else if (!isNaN(a[field]) && !isNaN(b[field]) ) {
                            sortCase = 'Number-Compare';
                            var av = +a[field], bv = +b[field];
                            var re = toggleFaktor * (av <= bv ? -1 : 1);
                        }
                        else if (typeof a[field] === 'string' && typeof b[field] === 'string') {
                            sortCase = 'String-Compare';
                            var av = a[field].toLowerCase(), bv = b[field].toLowerCase();
                            var re = toggleFaktor * (av <= bv ? -1 : 1);
                        } else {
                            sortCase = 'Other-Compare';
                            var av = a[field], bv = b[field];
                            var re = toggleFaktor * (av < bv ? -1 : +(av > bv));
                        }
                        // console.log(JSON.stringify({ func: 'cbSort', sortCase, field, dir, toggleFaktor, re, av, bv, a_field: a[field], b_field: b[field]}));
                        return re;
                    };
                    this.sort( cbSort ).renderBody();
                }
                return this;
            },
            searchByColFilter: function() {
                var colQueries = {};
                var filterRow = thead.find("tr.col-filters");
                var filterControls = filterRow.find(":input.col-filter-control");
                var usedControls = filterControls.filter( function() { return $(this).val() !== ''; });

                usedControls.each(function() {
                    colQueries[ $(this).closest("th").data( 'field') ] = $(this).val().toString().toLowerCase();
                });
                console.log('249 dataTable.renderer.searchByColFilter', colQueries, Object.keys(colQueries).length);

                var cbFilter = (!Object.keys(colQueries).length) ? null : function(row) {
                    console.log('252 cbFilter');
                    for(var _field in colQueries) {
                        if (!(_field in row)) {
                            console.error( '-- Query-Field Not Found in row ', { row });
                            return false;
                        }
                        var _val = row[ _field ];

                        if (null === _val || _val === '') {
                            console.debug( '-- Skip empty Field');
                            return false;
                        }

                        if (!~(_val.toString().toLowerCase().indexOf( colQueries[ _field ]))) {
                            console.log( '-- Not Found ' + colQueries[ _field ] + " in " + _field + ": " + _val.toString().toLowerCase());
                            return false;
                        }

                        console.log( '++ Found ' + colQueries[ _field ] + " in " + _field + ": " + _val.toString().toLowerCase());
                    }
                    console.log("+++ Row Matches Query", { row, colQueries });
                    return true;
                };

                return this.filter( cbFilter ).renderBody();
            },
            filter: function(cbFilter) {
                if (!cbFilter || typeof cbFilter !== 'function') {
                    this.useFilterResult = false;
                } else {
                    this.result = data.filter(cbFilter);
                    this.useFilterResult = this.result.length != data.length;
                }
                config.page = 1;
                return this;
            },
            sort: function(cbSort) {
                if (this.useFilterResult) {
                    this.result.sort( cbSort );
                } else {
                    data.sort( cbSort );
                }
                config.page = 1;
                return this;
            },
            init: function() {
                if ( !this.getElm().data('dataTableRendered') ) {
                    this.getElm().data({
                        'dataTableRendered': 1,
                        'dataTableRenderer': this,
                        data
                    });
                    this.render();
                }
                return this;
            },
            render: function() {
                this.renderHead();
                if (config.colfilters) {
                    this.renderColFilter();
                }
                this.renderBody();
                this.renderFoot();
                return this;
            },
            renderHead: function() {
                var $this = this;
                var colspan = Object.keys(config.fields).length;
                if (config.rownumbers) {
                    colspan+= 1;
                }
                hdrow.html('');

                if (config.title) {

                    var hdTrTitle = thead.find("tr.title").length
                        ? thead.find("tr.title")
                        : $("<tr>").addClass("title").prependTo(thead);

                    var hdThTitle = hdTrTitle.find('th').length
                        ? hdTrTitle.find('th')
                        : $("<th/>").attr("colspan", colspan).appendTo(hdTrTitle);

                    hdThTitle.html( config.title );
                }

                if (config.rownumbers) {
                    hdrow.append(
                        $('<th/>').addClass("col-nr").text( "#Nr" )
                    );
                }
                var cf = config.fields;
                for(var _col in cf) {
                    if ( cf[ _col].hidden ) {
                        continue;
                    }

                    var th = $("<th/>").attr('scope', 'col').addClass("col-" + _col).attr("data-field", _col).text( cf[_col].name )
                        .css({cursor: 'pointer'})
                        .on('click', function(col) {
                            console.log('auto order', { $this, th: this, col});
                            hdrow.find('.ordered-asc, .ordered-desc').not(this).removeClass('ordered-asc ordered-desc');
                            var col = $(this).data("field");
                            var dir = (
                                $(this).is('.ordered-asc')
                                ||
                                ($(this).is('.ordered-desc') && $(this).data("default-dir") === 'desc')
                            ) ? 'desc' : 'asc';

                            $(this)
                                .toggleClass('ordered-asc', dir === 'asc')
                                .toggleClass('ordered-desc', dir === 'desc');
                            return $this.orderby( col, dir);
                        })
                        .appendTo( hdrow );

                }
                return this;
            },
            renderColFilter: function() {
                // return this;
                var filterRow = thead.find("tr.col-filters").length
                    ? thead.find("tr.col-filters")
                    : $("<tr>").addClass("col-filters").appendTo(thead);

                filterRow.html('');


                if (config.rownumbers) {
                    $("<th/>").attr('scope', 'row').text( "O.").appendTo( filterRow );
                }


                var cf = config.fields;
                for(var _col in cf) {
                    if ( cf[ _col].hidden ) {
                        continue;
                    }

                    var th = $("<th/>")
                        .attr({'scope': 'col', "data-field": _col} )
                        .addClass("col-filter col-" + _col)
                        .appendTo( filterRow );

                    $("<input/>").attr({name: _col}).addClass("col-filter-control").appendTo(th)
                        .on('input', this.searchByColFilter.bind(this) );
                }
                return this;

            },
            renderBody: function() {
                var offset = (config.page - 1) * config.pagesize;
                var dataSource = this.useFilterResult ? this.result : data;
                var end = config.pagesize > 0 ? Math.min(dataSource.length, offset + config.pagesize) : dataSource.length;
                var cf = config.fields;

                tbody.html('');

                for(var tri = offset; tri < end; tri++ ) {

                    var _d = dataSource[ tri ];
                    var row = $("<tr/>").data("row", _d).appendTo( tbody );
                    if (config.key) {
                        row.attr("data-rowid", _d[config.key]);
                    }

                    if (config.rownumbers) {
                        $("<th/>").attr('scope', 'row').text( tri + 1 ).appendTo( row );
                    }

                    var flist = [];

                    for(var _col in cf) {
                        var _c = cf[ _col ];

                        if (_c.hidden || _c.colspan === 0) {
                            continue;
                        }

                        var csp = _c.colspan;
                        var val = _col in _d ? _d[ _col ] : null;

                        var cell = $("<td/>").attr("data-field", _col).addClass("col-" + _col).text( val ).appendTo( row );

                        if (csp > 1) {
                            cell.attr( 'colspan', csp);
                        }

                        if (_c.formatter) {
                            flist.push( _c.formatter.bind( cell, val, _col, row, _d) );
                        }

                    }
                    for(var fli = 0; fli < flist.length; fli++) {
                        flist[ fli ]();
                    }
                    if (config.rowformatter) {
                        config.rowformatter.call(row, _d);
                    }
                }

                if (config.onRendered && typeof config.onRendered === 'function') {
                    config.onRendered.apply(tbl, []);
                }

                return this;
            },
            renderPaging: function() {
                if (!_obj.paging) {
                    _obj.paging = $('<div/>').addClass('table-paging').appendTo( _obj.tfood);
                }
                _obj.paging.html('');
                var start = _obj.conf.page
            },
            renderFoot: function() {
                if (('showFooter' in config) && !config.showFooter) {
                    return;
                }

                var tfoot = tbl.find("tfoot").length ? tbl.find("tfoot") : $("<tfoot/>").appendTo( tbl );
                var tr = tfoot.find("tr").length ? tfood.find("tr") : $("<tr/>").appendTo( tfoot );
                var th = $("<th/>");
                var offset = 0;
                var numFooterCells = config.footer.length;
                var numCols = (config.rownumbers ? 1 : 0) + Object.keys(config.fields).length;

                config.footer.forEach( function(ftCnf, i) {
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
                    if ('formatter' in ftCnf && typeof ftCnf.formatter === 'function') {
                        ftCnf.formatter.apply( th, [config.fields]);
                    }
                })
            }
        };

        return renderer.setConfig( props ).init();
    })(selector, data, props);
}
