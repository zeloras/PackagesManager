var storageKey = 'holdTimeoutStorage';

var adminMainComponent = {
    config: {
        'slug_settings': {
            'delimiter': '-',
            'limit': undefined,
            'lowercase': true,
            'replacements': {},
            'transliterate': true
        },
        'template_clone_config': {
            'template': null,
            'template_in_class': null,
            'insert': null,
            'data': [],
            'bind': null,
            'callback': null,
            'input_group': 'input_group'
        }
    },

    init: function () {
        let self = this;
        let config = self.config;

        $().ready(function () {
            $(document).trigger('mainComponentsAdminLoaded');
        });

        $('a[data-delete]').unbind('click').on('click', function (e) {
            e.preventDefault();
            self.deleteLinkAction(this);
        });
    },

    /**
     * Hide/show some fields after change page type
     *
     * @param element
     * @param rules
     * @param cb
     */
    toggleFormGroup: function (element, rules, cb) {
        let self = adminMainComponent;
        let selected_type = $(element).val();
        rules = rules || {};

        for (let type in rules) {
            if (selected_type === type) {
                // off
                $(rules[type].off).closest('.form-group').hide();
                $(rules[type].off).removeAttr('required');

                // on
                $(rules[type].on).closest('.form-group').show();

                if (rules[type].require) {
                    $(rules[type].on).attr('required', 'true');
                }

                self.parseCallbackString(cb, false, [$(element), type]);

                break;
            } else {
                $(rules[type].off).closest('.form-group').hide();
                $(rules[type].off).removeAttr('required');
            }
        }
    },

    /**
     * Send post request with ID's for remove records
     *
     * @param elem
     */
    deleteMassive: function (elem) {
        let item = $(elem);
        let action = item.data('action');
        let inputs = $(item.data('inputs'));
        let token_val = item.data('token');
        let message = item.data('text');

        if (item.length && inputs.length) {
            let ids = '';
            let formData = new FormData();
            inputs.each(function () {
                ids += $(this).val() + ',';
            });
            formData.append('items', ids);

            let xhr = new XMLHttpRequest();

            xhr.onload = xhr.onerror = function() {
                document.location.reload();
            };

            xhr.open("POST", action, true);
            xhr.setRequestHeader('X-CSRF-TOKEN', token_val);
            if (message && message.length) {
                if (!confirm(message)) {
                    return false;
                }
            }
            xhr.send(formData);
        }
    },

    /**
     * Send request to remove element using get request
     *
     * @param elem
     */
    deleteLinkAction: function (elem) {
        let item = $(elem);
        let message = 'Are you sure you want to delete this item?';
        let href = '#';

        if (item.length) {
            message = item.data('delete') || message;
            href = item.attr('href') || href;
        }

        if (confirm(message)) {
            location.href = href;
        }
    },

    /**
     * Create a web friendly URL slug from a string.
     *
     * Requires XRegExp (http://xregexp.com) with unicode add-ons for UTF-8 support.
     *
     * Although supported, transliteration is discouraged because
     *     1) most web browsers support UTF-8 characters in URLs
     *     2) transliteration causes a loss of information
     *
     * @author Sean Murphy <sean@iamseanmurphy.com>
     * @copyright Copyright 2012 Sean Murphy. All rights reserved.
     * @license http://creativecommons.org/publicdomain/zero/1.0/
     *
     * @param text
     * @param configure
     * @return {string}
     */
    generateSlug: function (text, configure) {
        let self = adminMainComponent;
        let config = self.config;
        let xregexp = (typeof(XRegExp) === 'undefined') ? true : false;

        text = String(text);
        configure = Object(configure);

        let default_settings = config.slug_settings;
        default_settings.transliterate = xregexp;

        // Merge options
        for (let setting_key in default_settings) {
            if (!configure.hasOwnProperty(setting_key)) {
                configure[setting_key] = default_settings[setting_key];
            }
        }

        let char_map = {
            // Latin
            'À': 'A', 'Á': 'A', 'Â': 'A', 'Ã': 'A', 'Ä': 'A', 'Å': 'A', 'Æ': 'AE', 'Ç': 'C',
            'È': 'E', 'É': 'E', 'Ê': 'E', 'Ë': 'E', 'Ì': 'I', 'Í': 'I', 'Î': 'I', 'Ï': 'I',
            'Ð': 'D', 'Ñ': 'N', 'Ò': 'O', 'Ó': 'O', 'Ô': 'O', 'Õ': 'O', 'Ö': 'O', 'Ő': 'O',
            'Ø': 'O', 'Ù': 'U', 'Ú': 'U', 'Û': 'U', 'Ü': 'U', 'Ű': 'U', 'Ý': 'Y', 'Þ': 'TH',
            'ß': 'ss',
            'à': 'a', 'á': 'a', 'â': 'a', 'ã': 'a', 'ä': 'a', 'å': 'a', 'æ': 'ae', 'ç': 'c',
            'è': 'e', 'é': 'e', 'ê': 'e', 'ë': 'e', 'ì': 'i', 'í': 'i', 'î': 'i', 'ï': 'i',
            'ð': 'd', 'ñ': 'n', 'ò': 'o', 'ó': 'o', 'ô': 'o', 'õ': 'o', 'ö': 'o', 'ő': 'o',
            'ø': 'o', 'ù': 'u', 'ú': 'u', 'û': 'u', 'ü': 'u', 'ű': 'u', 'ý': 'y', 'þ': 'th',
            'ÿ': 'y',

            // Latin symbols
            '©': '(c)',

            // Greek
            'Α': 'A', 'Β': 'B', 'Γ': 'G', 'Δ': 'D', 'Ε': 'E', 'Ζ': 'Z', 'Η': 'H', 'Θ': '8',
            'Ι': 'I', 'Κ': 'K', 'Λ': 'L', 'Μ': 'M', 'Ν': 'N', 'Ξ': '3', 'Ο': 'O', 'Π': 'P',
            'Ρ': 'R', 'Σ': 'S', 'Τ': 'T', 'Υ': 'Y', 'Φ': 'F', 'Χ': 'X', 'Ψ': 'PS', 'Ω': 'W',
            'Ά': 'A', 'Έ': 'E', 'Ί': 'I', 'Ό': 'O', 'Ύ': 'Y', 'Ή': 'H', 'Ώ': 'W', 'Ϊ': 'I',
            'Ϋ': 'Y',
            'α': 'a', 'β': 'b', 'γ': 'g', 'δ': 'd', 'ε': 'e', 'ζ': 'z', 'η': 'h', 'θ': '8',
            'ι': 'i', 'κ': 'k', 'λ': 'l', 'μ': 'm', 'ν': 'n', 'ξ': '3', 'ο': 'o', 'π': 'p',
            'ρ': 'r', 'σ': 's', 'τ': 't', 'υ': 'y', 'φ': 'f', 'χ': 'x', 'ψ': 'ps', 'ω': 'w',
            'ά': 'a', 'έ': 'e', 'ί': 'i', 'ό': 'o', 'ύ': 'y', 'ή': 'h', 'ώ': 'w', 'ς': 's',
            'ϊ': 'i', 'ΰ': 'y', 'ϋ': 'y', 'ΐ': 'i',

            // Turkish
            'Ş': 'S', 'İ': 'I', 'Ç': 'C', 'Ü': 'U', 'Ö': 'O', 'Ğ': 'G',
            'ş': 's', 'ı': 'i', 'ç': 'c', 'ü': 'u', 'ö': 'o', 'ğ': 'g',

            // Russian
            'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G', 'Д': 'D', 'Е': 'E', 'Ё': 'Yo', 'Ж': 'Zh',
            'З': 'Z', 'И': 'I', 'Й': 'J', 'К': 'K', 'Л': 'L', 'М': 'M', 'Н': 'N', 'О': 'O',
            'П': 'P', 'Р': 'R', 'С': 'S', 'Т': 'T', 'У': 'U', 'Ф': 'F', 'Х': 'H', 'Ц': 'C',
            'Ч': 'Ch', 'Ш': 'Sh', 'Щ': 'Sh', 'Ъ': '', 'Ы': 'Y', 'Ь': '', 'Э': 'E', 'Ю': 'Yu',
            'Я': 'Ya',
            'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo', 'ж': 'zh',
            'з': 'z', 'и': 'i', 'й': 'j', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 'о': 'o',
            'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'c',
            'ч': 'ch', 'ш': 'sh', 'щ': 'sh', 'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu',
            'я': 'ya',

            // Ukrainian
            'Є': 'Ye', 'І': 'I', 'Ї': 'Yi', 'Ґ': 'G',
            'є': 'ye', 'і': 'i', 'ї': 'yi', 'ґ': 'g',

            // Czech
            'Č': 'C', 'Ď': 'D', 'Ě': 'E', 'Ň': 'N', 'Ř': 'R', 'Š': 'S', 'Ť': 'T', 'Ů': 'U',
            'Ž': 'Z',
            'č': 'c', 'ď': 'd', 'ě': 'e', 'ň': 'n', 'ř': 'r', 'š': 's', 'ť': 't', 'ů': 'u',
            'ž': 'z',

            // Polish
            'Ą': 'A', 'Ć': 'C', 'Ę': 'e', 'Ł': 'L', 'Ń': 'N', 'Ó': 'o', 'Ś': 'S', 'Ź': 'Z',
            'Ż': 'Z',
            'ą': 'a', 'ć': 'c', 'ę': 'e', 'ł': 'l', 'ń': 'n', 'ó': 'o', 'ś': 's', 'ź': 'z',
            'ż': 'z',

            // Latvian
            'Ā': 'A', 'Č': 'C', 'Ē': 'E', 'Ģ': 'G', 'Ī': 'i', 'Ķ': 'k', 'Ļ': 'L', 'Ņ': 'N',
            'Š': 'S', 'Ū': 'u', 'Ž': 'Z',
            'ā': 'a', 'č': 'c', 'ē': 'e', 'ģ': 'g', 'ī': 'i', 'ķ': 'k', 'ļ': 'l', 'ņ': 'n',
            'š': 's', 'ū': 'u', 'ž': 'z'
        };

        // Make custom replacements
        for (let config_key in configure.replacements) {
            text = text.replace(RegExp(config_key, 'g'), configure.replacements[config_key]);
        }

        // Transliterate characters to ASCII
        if (configure.transliterate) {
            for (let char_key in char_map) {
                text = text.replace(RegExp(char_key, 'g'), char_map[char_key]);
            }
        }

        // Replace non-alphanumeric characters with our delimiter
        let clear_symbl = (xregexp) ? RegExp('[^a-z0-9\\/]+', 'ig') : XRegExp('[^\\p{L}\\p{N}]+', 'ig');
        text = text.replace(clear_symbl, configure.delimiter);

        // Remove duplicate delimiters
        text = text.replace(RegExp('[' + configure.delimiter + ']{2,}', 'g'), configure.delimiter);

        // Truncate slug to max. characters
        text = text.substring(0, configure.limit);

        // Remove delimiter from ends
        text = text.replace(RegExp('(^' + configure.delimiter + '|' + configure.delimiter + '$)', 'g'), '');

        return configure.lowercase ? text.toLowerCase() : text;
    },

    appendTemplate: function (...args) {
        let self = adminMainComponent;
        let config = self.config;
        let settings = config.template_clone_config;
        let arguments_list = (args.hasOwnProperty(0)) ? args[0] : {};
        let replaceFunc = function (template, arr, key) {
            let value = arr[key];
            let name = (/^\_/.test(key)) ? '%' + key.replace(/^\_/, '') + '%' : '\\%data\\.' + key + '\\%';
            value = (typeof value === "array" || typeof value === "object") ? JSON.stringify(value) : value;

            if (arr.hasOwnProperty('is.data.main') && arr['is.data.main'] && /^is\.data\./.test(key) && !/^is\.data\.main/.test(key)) {
                name = '\\%' + key + '\\%';
                value = (value) ? "disabled='disabled' checked='checked'" : "disabled='disabled'";
            } else if (/^is\.data\.main/.test(key)) {
                name = '\\%' + key + '\\%';
                value = (value) ? "main_block" : "";
            } else if (/^is\.data\./.test(key) && !/^is\.data\.main/.test(key)) {
                name = '\\%' + key + '\\%';
                value = (value) ? "checked='checked'" : "";
            }

            template = template.replace(new RegExp(name, 'igm'), value);

            return template;
        };

        /**
         * For reinit settings
         */
        for (let config_key in settings) {
            if (arguments_list.hasOwnProperty(config_key)) {
                settings[config_key] = arguments_list[config_key];
            }
        }

        if (settings.template && settings.insert) {
            if (settings.data && Array.isArray(settings.data)) {
                let template_block = $(settings.template);
                let template_in = settings.template_in_class || settings.template;

                if (template_block.length) {
                    let template_clone = $(template_block[0].content).find(template_in).clone();
                    let template_item = template_clone.data('elements');

                    for (let arr of settings.data) {
                        let template = template_clone.html();
                        let arr_list = {};
                        arr['_input_group'] = settings.input_group;
                        arr['_id'] = (Number(arr['id']) === 0) ? self.countCloneGroups($(settings.insert), template_item) : arr['id'];
                        template = replaceFunc(template, arr, '_id');

                        for (let key in arr) {
                            if (!/^_/.test(key)) {
                                arr_list['is.data.' + key] = arr[key];
                            }

                            arr_list[key] = arr[key];
                        }

                        for (let key in arr_list) {
                            template = replaceFunc(template, arr_list, key);
                        }

                        $(settings.insert).append(template);
                    }
                } else {
                    console.error('Dosent find template block')
                }
            } else {
                console.error('Dosent set data or data dosent array')
            }
        } else {
            console.error('Dosent set settings for template or insert block');
        }
        //self.parseCallbackString(cb)($(element), type);
    },

    /**
     * Function for parse and try call string as function
     *
     * @param callback
     * @param call
     * @param args
     * @return {(function(): null)|*}
     */
    parseCallbackString: function (callback, call, ...args) {
        let callback_function = function () {return null};
        let arguments_list = args;
        let called = window;
        call = call || false;

        if (typeof callback == "function" || typeof callback == "string" && callback.length) {
            callback_function = callback;

            if (typeof callback !== "function") {
                let can_call = true;
                let try_split = callback.split('.');

                for (let part in try_split) {
                    if (called.hasOwnProperty(try_split[part])) {
                        called = called[try_split[part]];
                    } else {
                        can_call = false;
                    }
                }

                if (can_call) {
                    callback_function = called;
                }
            }
        }

        if (arguments_list.length) {
            return callback_function.apply(null, ...arguments_list);
        } else {
            return (call) ? callback_function() : callback_function;
        }
    },

    /**
     * Find available id for formname
     *
     * @param clone_list Class with cloned items block
     * @param clone_item Class item block
     * @returns {number}
     */
    countCloneGroups: function (clone_list, clone_item) {
        clone_item = clone_item || false;
        let container = $(clone_list);
        let regexp = new RegExp('\\[(\\d+)\\]', 'is');
        let items = (clone_item) ? container.find(clone_item) : {};
        let counts = [];
        let id = 0;
        let findByName = '';

        if (clone_item) {
            items.each(function () {
                let item = $(this).find('input:eq(0)').attr('name');
                let cnt = item.match(regexp);
                counts.push(Number(cnt[1]));
                findByName = item;
            });

            for (let count_item of counts) {
                let nameFind = findByName.replace(regexp, '[' + Number(count_item + 1) + ']');

                if (container.find('input' + nameFind).length < 1) {
                    id = Number(count_item + 1);
                }
            }
        }

        return id;
    },

    /**
     * Hold time
     *
     * @param alias
     * @param fn
     * @param time
     */
    holdTimeout: function (alias, fn, time) {
        let self = adminMainComponent;
        function _clear() {
            clearInterval(window[storageKey][alias]);
            window[storageKey][alias] = false;
        }

        if (!self.getObjectValue(window, storageKey)) {
            window[storageKey] = {};
        }

        _clear();

        if (!self.getObjectValue(window[storageKey], alias)) {
            window[storageKey][alias] = setTimeout(function () {
                fn();
                _clear();
            }, time);
        }
    },

    /**
     * Get value from object by key
     *
     * @param obj
     * @param key
     * @param defaultValue
     * @return {*}
     */
    getObjectValue: function (obj, key, defaultValue) {
        let self = adminMainComponent;
        defaultValue = defaultValue || null;

        if (typeof obj === 'undefined') {
            return defaultValue;
        }

        let _index = key.indexOf('.');

        if (_index > -1) {
            return self.getObjectValue(
                obj[key.substring(0, _index)],
                key.substr(_index + 1),
                defaultValue
            );
        }

        return obj[key] || defaultValue;
    },

    /**
     * Fill values to form by element
     *
     * @param element
     * @param value
     */
    setFormElementValue: function (element, value) {
        let tag = element.prop('tagName');
        let tagType = element.attr('type');


        // type=text
        if (element.is('input')) {
            let type = element.attr('type');
            if (type == 'text' || type == 'number' || type == 'hidden') {
                element.val(value);
            }
        }

        // type=checkbox
        if (element.is('input') && element.attr('type') == 'checkbox') {
            element.prop('checked', (value == "1") ? true : false);
        }

        // type=textarea
        if (element.is('textarea')) {
            element.val(value);
        }

        // type=select
        if (element.is('select')) {
            element.find('option[value="' + value + '"]').attr('selected', true);
            element.trigger('change');
        }
    }
};

adminMainComponent.init();