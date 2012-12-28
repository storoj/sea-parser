function callFunctionList(funcList, context, arguments){
    if (funcList instanceof Array) {
        for(var i=0; i<funcList.length; ++i){
            if(typeof funcList[i] === 'function'){
                funcList[i].apply(context, arguments);
            }
        }
        return;
    }

    if (typeof funcList === 'function') {
        funcList.apply(context, arguments);
    }
}

function setDebugInfo(type, info, url) {
    if ($('#debug_content').length) {
        switch(type) {
            case 'error':
                $('#debug_content').append('' +
                    '<p><span class="debug_def">[E]</span> '
                    + ' :: <span class="debug_table">'
                    + info + '</span></p>');
                break;
            case 'info':
                $('#debug_content').append('<p><span class="debug_url">'
                    + url + '</span> :: <span class="debug_table">'
                    + info.exec_time + '</span> msec</p>');
                for (var i = 0; i < info.debug_info.length; i++) {
                    $('#debug_content').append(info.debug_info[i]);
                }
                break;
        }
    }
}

function positionLoader(){
    var elem = $('.preloader');
    var wnd = $(window).scrollTop();
    var wndHeight = $(window).height();
    var top = wnd + (wndHeight/2) - (elem.height()/2);
    var wndWidth = $(window).width();
    var left = (wndWidth/2) - (elem.width()/2);
    elem.css({'position':'absolute','top':top,'left':left});
}

/*function refreshSelect(select) {
    $(select)
        .selectBox('destroy')
        .selectBox()
}*/

/*function refreshChecks(checks, box, attr) {
    if (typeof box === 'undefined') box = '.ez-checkbox';
    if (typeof attr === 'undefined') attr = 'ez-checked';

    if (typeof checks === 'string') {
        checks = $(checks).find('input[type="checkbox"]');
    }

    $(checks).each(function(i, item){
        console.log($(item).closest(box));
        console.log($(item).attr('checked'));
        if ($(item).attr('checked')) {
            $(item).closest(box).addClass(attr);
        } else {
            $(item).closest(box).removeClass(attr);
        }
    });
}*/

function AjaxQuery(params, data, properties){
    var defaults = {
        type: 'json',
        hashable: false,
        output: false,
        output_method: 'replace',
        pager: false,
        preloader: false,
        reload: false,
        callbacks: {
            before: [],
            error: [],
            success: [],
            rollback: []
        },
        autoexecute: false,
        request_type: 'POST',
        show_alert: true
    };

    var switchers = new Array( 'select', 'up', 'blur', 'change', 'click' );

    $.extend(this, {
        blocked: false,
        settings: $.extend(defaults, params),
        data: {},
        saved_state: {},
        properties: {},

        execute: function(){
            /* call actions on before data send (if there are some)  */
            callFunctionList(this.settings.callbacks.before, this, [this.data]);

            if(this.blocked){
                return false;
            }
            this.blocked = true;

            // showing preloader if needed
            if (this.settings.preloader.length) {
                var preloader = $(this.settings.preloader);
                if (preloader.length) {
                    preloader.fadeIn(100);
                    positionLoader();
                }
            }

            console.log(this);

            $.ajax(
                this.settings.url,
                {
                    async: true,
                    context: this,
                    data: {data: this.data},
                    dataType: this.settings.type,
                    type: this.settings.request_type,

                    complete: function(){
                        console.log('AJAX :: query complete');
                        this.blocked = false;

                        if (this.settings.preloader.length) {
                            var preloader = $(this.settings.preloader);
                            if (preloader.length) preloader.fadeOut(100);
                        }
                    },

                    error: function(jqXHR, textStatus, errorThrown){
                        console.log('AJAX :: query error');
                        console.log('error: ' + textStatus);
                        console.log(jqXHR);

                        /* debug */
                        setDebugInfo('error', textStatus);
                    },

                    success: function(response, textStatus, jqXHR){
                        console.log('AJAX :: query success');
                        console.log(response);

                        this.setOutputContent(response);

                        /* showing debug info (if needed) */
                        if (typeof(response.debug_info) !== 'undefined') {
                            setDebugInfo('info', response, this.settings.url);
                        }

                        /* show error text on error response status */
                        /*if (this.settings.show_alert && typeof response.msg !== 'undefined') {
                            alertbox(response.msg, response.status);
                        }

                        /* callbacks execution */
                        callFunctionList(this.settings.callbacks.success, this, [response.status, response]);

                        /* save query state for possible rollback */
                        this.saveState();

                        if (this.settings.hashable)  {
                            /* setting new hash */
                            this.setHashValues();

                            /* setting switchers (may have been changed by rollback)*/
                            this.setSwitchers();
                        }
                    }
                }
            );
        },

        autoexecute: function(){
            if (this.settings.autoexecute){
                this.execute();
            }
        },

        rollback: function(param) {
            if (typeof param === 'undefined') {
                $.extend(this.data, this.saved_state);
            } else if (typeof param === 'string'
                && typeof this.saved_state[param] !== 'undefined') {

                this.data[param] = this.saved_state[param];
            }

            if (this.settings.callbacks.rollback.length) {
                callFunctionList(this.settings.callbacks.rollback);
            }
        },

        setOutputContent: function(response){
            if(false !== this.settings.output){
                var outputElement = $(this.settings.output);

                if(outputElement.length > 0){
                    switch(this.settings.output_method){
                        case 'replace':
                            outputElement.html(response.content);
                            break;
                        case 'append':
                            break;
                    }

                } else {
                    alert('no output block '+this.settings.output);
                }
            }

            if(false !== this.settings.pager){
                var pagerElement = $(this.settings.pager);
                if(pagerElement.length > 0){
                    pagerElement.html(response.pager);
                } else {
                    alert('no pager block '+this.settings.pager);
                }
            }
        },

        setKeyValue: function(keyValue, value){
            var autoExecute = this.settings.autoexecute;
            this.settings.autoexecute = false;

            if(typeof keyValue === 'object'){
                for(var key in keyValue){
                    this.set(key, keyValue[key]);
                }
            } else {
                this.set(keyValue, value);
            }

            this.settings.autoexecute = autoExecute;
            this.autoexecute();

            return this;
        },

        removeValue: function(key) {
            if (this.data[key] !== 'undefined') {
                delete this.data[key];
                this.setHashValues();
            }
        },

        set: function(key, value){
            var execute = true;

            var setter = 'set' + key.charAt(0).toUpperCase() + key.substring(1);
            if(this.hasOwnProperty(setter) && typeof this[setter] === 'function'){
                var fn = this[setter];
                fn.call(this, value);
            } else {
                // change and refresh only if value was really changed
                if (this.data[key] != value) {
                    this.data[key] = value;
                } else {
                    execute = false;
                }
            }

            if (execute) this.autoexecute();
        },

        saveState: function() {
            this.saved_state = {};
            for(var key in this.data) {
                this.saved_state[key] = this.data[key];
            }
        },

        addCallback: function(callback, eventType){
            if(typeof callback === 'function'){
                if(typeof eventType === 'undefined'){
                    eventType = 'success';
                }
                this.settings.callbacks[eventType].push(callback);
            }
        },

        getHashValues: function() {
            if (!document.location.hash.match('^#!')) {
                return this;
            }

            var hash = document.location.hash.replace('#!', '').split('&');
            var values = {};
            for(var i = 0; i < hash.length; ++i){
                var hashVar = hash[i].split('=');
                if (hashVar.length > 0){
                    var hashVarName = hashVar[0];
                    values[hashVarName] = decodeURIComponent(hashVar[1]);
                }
            }

            this.setKeyValue(values);

            return this;
        },

        setHashValues: function() {
            var hashString = [];
            for(var key in this.data){
                hashString.push( key + '=' + this.data[key]);
            }
            document.location.hash = '#!'+ hashString.join('&');
        },

        setSwitchers: function(){
            var finder = function(object){
                var data = $(object).data('switcher');
                if (typeof data !== 'undefined') {
                    return data.split('@')[1].split('.')[1];
                } else {
                    return false;
                }
            };

            for (var type in switchers) {
                var obj_list = $('.switcher_' + switchers[type]);
                if (obj_list.length) {
                    switch(switchers[type]) {
                        case 'select':
                            for (var i = 0; i < obj_list.length; i++) {
                                var param_name = finder(obj_list[i]);
                                if (param_name) {
                                    $(obj_list[i])
                                        .find('option[value="'+this.data[ param_name ]+'"]')
                                        .attr('selected', 'selected');
                                    refreshSelect(obj_list[i]);
                                }
                            }
                            break;
                        case 'up':
                        case 'blur':
                            for (var i = 0; i < obj_list.length; i++) {
                                var value = finder(obj_list[i]);
                                if (value != 'undefined') {
                                    $(obj_list[i]).val(this.data[ value ]);
                                }
                            }
                            break;
                        case 'change':
                            for (var i = 0; i < obj_list.length; i++) {
                                // find value items list
                                var item_list = $(obj_list[i]).find('.switcher-value');
                                if (!item_list.length)
                                    continue;

                                var node = item_list[0].nodeName.toLowerCase();
                                if (node == 'input') {
                                    // if it is input check its type
                                    node = $(item_list[0]).attr('type');
                                }
                                var values = this.data[ finder(obj_list[i]) ].split(',');
                                if (!values.length || (values.length == 1 && values[0] == ''))
                                    continue;

                                switch(node) {
                                    case 'checkbox':
                                        for(var j = 0; j < item_list.length; j++) {
                                            if (in_array($(item_list[j]).attr('value'), values)) {
                                                //console.log('setting checked :: ' + item_list[j] + ' - ' + $(item_list[j]).attr('value'));
                                                $(item_list[j]).attr('checked', 'checked');
                                            } else {
                                                $(item_list[j]).removeAttr('checked');
                                            }
                                            //$(item_list[i]).trigger('change');
                                        }
                                        break;
                                    case 'radio':
                                        for(var j = 0; j < item_list.length; j++) {
                                            if ($(item_list[j]).attr('value') == values[0]) {
                                                $(item_list[j]).attr('checked', 'checked')
                                                    .trigger('change');
                                                break;
                                            }
                                        }
                                        break;
                                }
                            }
                            break;
                        case 'click':
                            /* ?? */
                            break;
                    }
                }
            }
        }
    });

    if(typeof data !== 'undefined'){
        this.setKeyValue(data);
    }

    this.saved_state = this.data;

    if(typeof properties !== 'undefined'){
        this.properties = properties;
    }

    if (this.settings.hashable) {
        this.getHashValues();

        this.setSwitchers();
    }

    return this;
}

function AjaxHandler(){
    $.extend(this, {
        queries: {},

        addQuery: function(query, alias){
            if(query instanceof AjaxQuery){
                if(typeof this.queries[alias] !== 'undefined'){
                    alert('Error: query with alias <b>'+alias+'</b> already exists!');
                }

                this.queries[alias] = query;
                return true;
            }
            return false;
        },

        getQuery: function(alias){
            if(typeof alias !== 'undefined'){
                if(typeof this.queries[alias] !== 'undefined'){
                    return this.queries[alias];
                } else {
                    alert('Error: query with alias <b>'+alias+'</b> does not exist!');
                }
            }

            return false;
        },

        execute: function(alias){
            if(typeof alias !== 'undefined'){
                var query = this.getQuery(alias);
                if(false !== query){
                    query.execute();
                }
                return;
            }

            for(var i in this.queries){
                this.queries[i].execute();
            }
        },

        getAutoExecQueries: function(){
            var result = new AjaxHandler();

            for(var i in this.queries){
                if(this.queries[i].settings.autoexecute){
                    result.addQuery(this.queries[i]);
                }
            }

            return result;
        },

        clearHash: function(){
            // some control is possible
            document.location.hash = '';
        }
    });
}

var ajaxHandler = new AjaxHandler();

function initiateSwitcher(object, value, force, rollback) {
    var dataSwitcher = $(object).data('switcher').split('@');
    var queryAlias = dataSwitcher[0];

    var query = ajaxHandler.getQuery(queryAlias);
    if(query instanceof AjaxQuery){
        if (rollback === true || $(object).hasClass('rollback')) {
            query.rollback();
        }
        checkReset(object);

        var paramName = dataSwitcher[1].split('.');
        var paramType = paramName[0];
        paramName = paramName[1];

        if(paramType == 'data'){
            query.set(paramName, value);
        }

        if (force === true || $(object).hasClass('reload')) {
            query.execute();
        } else {
            query.autoexecute();
        }
    }

    return false;
}

function checkReset(switcher, reset) {
    if (typeof switcher === 'undefined') {
        return false;
    }

    var query_alias = $(switcher).data('switcher').split('@')[0];
    if (typeof reset === 'undefined') {
        reset = $(switcher).data('reset');
    }

    if (typeof reset !== 'undefined') {
        var query = ajaxHandler.getQuery(query_alias);
        if (query !== false) {
            var reset_list = reset.split(';');
            for (var i in reset_list) {
                var params = reset_list[i].split('@');
                if (typeof query.data[params[0]] !== 'undefined') {
                    query.data[params[0]] = params[1];
                }
            }
        }
    }

    return false;
}

$(document).ready(function(){

    $('body').on('click', '.submit', function(){
        var query_alias = $(this).data('switcher');
        checkReset(this);
        ajaxHandler.execute(query_alias);
    });

    // switcher for select boxes
    $('.switcher_select').on('change', function(){
        var value = $(this).find('option:selected').val();
        initiateSwitcher(this, value);
    });

    $('input.switcher_up, textarea.switcher_up').on('keyup', function() {
        var value = $(this).val();
        initiateSwitcher(this, value);
    });

    $('input.switcher_blur, textarea.switcher_blur').on('blur', function() {
        var value = $(this).val();
        initiateSwitcher(this, value);
    });

    $('.switcher_change').on('change', '.switcher-value', function(e){
        var node = this.nodeName.toLowerCase();
        var parent = $(this).closest('.switcher_change');

        if (node == 'input') {
            var type = $(this).attr('type');

            if (type != 'undefined') {
                var value = false;
                switch(type) {
                    case 'checkbox':
                        var list = new Array();
                        parent.find('.switcher-value:checked').each(function(i, item){
                            list.push($(item).attr('value'));
                        });
                        value = list.join(',');
                        break;
                    case 'radio':
                        value = parent.find('.switcher-value:checked').attr('value');
                        break;
                }

                if (value !== false) {
                    initiateSwitcher(parent, value);
                } else {
                    console.log('error setting "switcher_change" value');
                }
            }
        }
    });

    $('.switcher_click').on('click', '.switcher-value', function(){
        var parent = $(this).closest('.switcher_click');
        var value = $(this).attr('value');
        console.log(value);

        checkReset(parent, $(this).data('reset'));

        // here we check if we need to get several values for this switcher
        /*if (parent.data('multiple') != 'undefined') {
            var active_class = parent.data('multiple');
            var list = new Array();
            parent.find('.switcher-value').each(function(i, item) {
                if ($(item).hasClass(active_class)) {
                    list.push($(item).attr('value'));
                }
            });

            value = list.join(',');
        }*/

        initiateSwitcher(parent, value);

        return false;
    });
});

/*
 $.extend(query, {
 setName: function(name){
 if(name.length == 0){
 console.log('name is empty!');
 return false;
 }
 this.data['name'] = name;
 return true;
 },

 setPage: function(page){
 console.log('page: '+page);
 if(parseInt(page) > 0){
 this.data['page'] = page;
 return true;
 }

 console.log('invalid page');
 return false;
 }
 });*/


/*function alertbox(content, type, autohide){
    if(typeof autohide === 'undefined'){
        autohide = true;
    }
    autohide = !!autohide;
    console.log(autohide);
    type = typeof type !== 'undefined' ? type : '';

    if(type != ''){
        type = 'alert-'+type;
    }
    var time = new Date().getTime();
    var itemID = 'notification-'+time;
    var alertHTML = '<div class="alert alert-block '+type+' fade in" id="'+itemID+'">'
        +'<button class="close" data-dismiss="alert">×</button><strong>'
        + content
        +'</strong></div>';

    $('#alertsContainer').prepend(alertHTML);


    autohide && setTimeout(function(){
        $('#'+itemID).fadeOut(300, function(){
            $(this).remove()
        })
    }, 4000);

    $('.alert .close').click(function(){
        $(this).parent().remove();
    });
}

function alert(message, autohide){
    alertbox(message, 'error', autohide)
}

function alert(message, autohide){
    alertbox(message, 'error', autohide)
}*/