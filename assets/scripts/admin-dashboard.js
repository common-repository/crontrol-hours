/*!
    Name: admin-dashboard.js
    Author: AuRise Creative | https://aurisecreative.com
    Last Modified: 2023.11.09.14.39
*/
window['$'] = window['$'] || jQuery.noConflict();
const auPluginAdminDashboard = {
    version: '2023.03.01.23.24',
    init: function() {
        //Plugin initialization
        auPluginAdminDashboard.tabs.init();
        auPluginAdminDashboard.forms.init();
        auPluginAdminDashboard.accordion.init();

        //Custom plugin functionality
        $('.au-plugin form#crontrol-hours-update').on('submit', auPluginAdminDashboard.updateHours.start);

        //Init complete, display admin UI
        auPluginAdminDashboard.initComplete();
    },
    initComplete: function() {
        //If there is a Hash in the URL, open that tab
        let current_tab = document.location.hash;
        if (current_tab && $(current_tab).length) {
            //open the current tab
            auPluginAdminDashboard.tabs.open(current_tab.replace('#', ''));
        } else {
            //open first tab
            auPluginAdminDashboard.tabs.open($('.au-plugin a.nav-tab').first().attr('href').replace('#', ''));
        }
        //init is completed. Hide loading spinner image and display the admin UI
        $('.au-plugin .loading-spinner').addClass('hide');
        $('.au-plugin .admin-ui').removeClass('hide');
    },
    tabs: {
        init: function() {
            //Hide all tabs
            $('.au-plugin section.tab').addClass('hide');

            //Add button listeners
            let $btns = $('.au-plugin a.nav-tab');
            $btns.on('click', auPluginAdminDashboard.tabs.handler);
            if ($btns.length === 1) {
                //If there's only one tab, hide them
                $('.au-plugin .nav-tab-wrapper').addClass('hide');
            }
        },
        handler: function(event) {
            event.preventDefault();
            let tab = $(this).attr('href').replace('#', '');
            auPluginAdminDashboard.tabs.open(tab);
        },
        open: function(tab) {
            $('.au-plugin a.nav-tab, .au-plugin #tab-content section.tab').removeClass('nav-tab-active'); //Deactivate all of the tab buttons and tab contents
            $('.au-plugin #tab-content section.tab').addClass('hide'); //Hide all of the tab contents
            $('.au-plugin #' + tab).removeClass('hide').addClass('nav-tab-active'); //Show and activate the tab content
            $('.au-plugin #open-' + tab).addClass('nav-tab-active'); //Activate the tab button
        }
    }, // End of `tabs`
    forms: {
        init: function() {
            auPluginAdminDashboard.forms.initSwitches();
            auPluginAdminDashboard.forms.initColorPickers();
            auPluginAdminDashboard.forms.controlledFields.init();
        },
        initSwitches: function() {
            //Add checkbox listeners for switch toggles
            let $checkboxes = $('.au-plugin input[type="hidden"]+input[type="checkbox"]');
            if ($checkboxes.length) {
                $('.au-plugin input[type="hidden"]+input[type="checkbox"]').on('click', auPluginAdminDashboard.forms.switchHandler);
            }
        },
        switchHandler: function(e) {
            // Updates the hidden field with the boolean value of the checkbox
            let $input = $(this),
                checked = $input.is(':checked') || $input.prop('checked');
            if ($input.hasClass('reverse-checkbox')) {
                //Reverse checkboxes show a positive association with the "false" value
                $input.siblings('input[type="hidden"]').val(checked ? '0' : '1');
            } else {
                $input.siblings('input[type="hidden"]').val(checked ? '1' : '0');
            }
            // Exclusive switches
            if (checked) {
                let input_name = $(this).attr('name'),
                    force_daily = input_name.indexOf('force_daily') > -1,
                    restrict_frequent = input_name.indexOf('restrict_frequent') > -1;
                if (force_daily) {
                    // Uncheck restrict frequent
                    auPluginAdminDashboard.forms.toggleCheckbox('#crontrol_hours_restrict_frequent_check', false);
                } else if (restrict_frequent) {
                    // Uncheck force daily
                    auPluginAdminDashboard.forms.toggleCheckbox('#crontrol_hours_force_daily_check', false);
                }
            }
        },
        getCheckbox: function(input) {
            //Returns a true/false boolean value based on whether the checkbox is checked
            let $input = $(input);
            return ($input.is(':checked') || $input.prop('checked'));
        },
        toggleCheckbox: function(input, passedValue) {
            //Changes a checkbox input to be checked or unchecked based on boolean parameter (or toggles if not included)
            //Only changes it visually - it does not change any data in any objects
            let $input = $(input),
                value = passedValue,
                $hidden;
            if (typeof(value) != 'boolean' || value === undefined) {
                value = !auPluginAdminDashboard.forms.getCheckbox($input);
            }
            if (value) {
                $input.attr('checked', 'checked');
                $input.prop('checked', true);
                $hidden = $input.siblings('input[type="hidden"]');
                // Also update the hidden value if this checkbox is a toggle switch
                if ($hidden.length) {
                    if ($input.hasClass('reverse-checkbox')) {
                        //Reverse checkboxes show a positive association with the "false" value
                        $hidden.val(value ? '0' : '1');
                    } else {
                        $hidden.val(value ? '1' : '0');
                    }
                }
            } else {
                $input.removeAttr('checked');
                $input.prop('checked', false);
                // Also update the hidden value if this checkbox is a toggle switch
                $hidden = $input.siblings('input[type="hidden"]');
                if ($hidden.length) {
                    if ($input.hasClass('reverse-checkbox')) {
                        //Reverse checkboxes show a positive association with the "false" value
                        $hidden.val(value ? '0' : '1');
                    } else {
                        $hidden.val(value ? '1' : '0');
                    }
                }
            }
        },
        initColorPickers: function() {
            let $colorPickers = $('.au-plugin input[type="text"].au-color-picker');
            if ($colorPickers.length) {
                try {
                    //Add color picker to each individual picker
                    $colorPickers.each(function(i, el) {
                        $(el).wpColorPicker({
                            'change': function(e, ui) {
                                let id = $(e.target).attr('name').replace('accessible_reading_toggle_switch_', '').replaceAll('_', '-'),
                                    color = ui.color.toString();
                                auPluginAdminDashboard.updateTogglePreviewColor(id, color);
                            },
                            'clear': function(e) {
                                let $input = $(e.target);
                                if (!$input.is('input') || !$input.hasClass('wp-color-picker')) {
                                    $input = $(e.target).siblings('label').children('.wp-color-picker');
                                }
                                if ($input.length) {
                                    let id = $input.attr('name').replace('accessible_reading_toggle_switch_', '').replaceAll('_', '-');
                                    auPluginAdminDashboard.updateTogglePreviewColor(id, $input.data('default'));
                                }
                            }
                        });
                    });
                } catch (xhr) {
                    console.error('Color Picker Error', xhr);
                }
            }
        },
        controlledFields: {
            /*
                To use this feature...

                1. Add a "controller" class to the radio, checkbox, or select HTML elements that will be controlling others
                    - Checkbox: displays the controlled fields when checked and hides when unchecked.
                    - Radio:    displays the controlled fields when checked and hides the rest.
                    - Select:   displays the controlled fields when they match the value that is selected and hides the rest.
                2. Controlled fields should have a data-controller attribute on its wrapping element set to the unique ID of its controller
                3. Controlled fields should have a "hide" class added to its wrapping element to hide it by default. This feature simply toggles that class on/off, so you'll need CSS to actually hide it based on that class.
                4. If it is controlled by a radio button or select element, the wrapping element of the controlled field should also have a data-values attribute set to a comma separated list of the values used to display it.
                5. If the controlled field should be required when displayed, instead of adding the required attribute to the input/select field, add the data-required="true" attribute.
                6. It is possible to nest controllers.
            */
            init: function() {
                //Add controllable field listeners
                if ($('.au-plugin input[type=checkbox].controller, .au-plugin input[type=radio].controller, .au-plugin select.controller').length) {
                    $('.au-plugin input[type=checkbox].controller, .au-plugin input[type=radio].controller').on('click', auPluginAdminDashboard.forms.controlledFields.toggleHandler);
                    $('.au-plugin select.controller').on('change', auPluginAdminDashboard.forms.controlledFields.toggleHandler);
                    $('.au-plugin input[type=checkbox].controller, .au-plugin input[type=radio].controller, .au-plugin select.controller').each(function() {
                        let $controller = $(this),
                            id = $controller.attr('id'),
                            $controlled = $('[data-controller="' + id + '"]');
                        if ($controlled.length) {
                            let controlled_value = $controller.is('input[type=checkbox]') ? auPluginAdminDashboard.forms.getCheckbox($controller) : $controller.val();
                            auPluginAdminDashboard.forms.controlledFields.toggleControlledFields(id, controlled_value);
                        } else {
                            console.warn('Controlled fields for Controller #' + id + ' do not exist!');
                        }
                    });
                }
            },
            toggleHandler: function(e) {
                let $controller = typeof(e) == 'string' ? $('#' + e) : $(this),
                    id = $controller.attr('id');
                auPluginAdminDashboard.forms.controlledFields.toggleControlledFields(id, null);
            },
            toggleControlledFields: function(id, forcedToggle) {
                let $controller = $('#' + id);
                if ($controller.length < 1) { console.warn('Controller #' + id + ' does not exist!'); return; }
                //console.info('Toggle Fields: ' + id);
                let $controlled = $('[data-controller="' + id + '"]');
                if ($controlled.length < 1) { console.warn('Controlled fields for Controller #' + id + ' do not exist!'); return; }
                if ($controller.is('select')) {
                    let controlled_value = forcedToggle === null || forcedToggle === undefined ? $controller.val() : forcedToggle;
                    //Because it is a select field, the value must match that of the input to display it
                    $controlled.each(function() {
                        let $thisControlled = $(this),
                            myValues = $thisControlled.data('values');
                        if (myValues.indexOf(',') >= 0) {
                            myValues = myValues.split(',');
                        } else {
                            myValues = [myValues];
                        }
                        let matches = 0;
                        $.each(myValues, function(i, value) {
                            if (value == controlled_value) { matches++; }
                        });
                        if (matches > 0) {
                            //This controlled element's value matches what was selected in the dropdown, display it
                            $thisControlled.removeClass('hide');
                            //If there are any required fields, add the required flag to them
                            let $required_fields = $thisControlled.find('[data-required="true"]');
                            if ($required_fields.length > 0) {
                                $required_fields.each(function() {
                                    $(this).attr('required', 'required');
                                });
                            }
                        } else {
                            //This controlled element's value does not match what was selected in the dropdown, hide it
                            //Checkbox or radio button is false, so hide its options
                            $thisControlled.addClass('hide');
                            //If there are any required fields, remove the required flag from them
                            let $required_fields = $thisControlled.find('[required]');
                            if ($required_fields.length > 0) {
                                $required_fields.each(function() {
                                    $(this).removeAttr('required');
                                });
                            }
                            //Search through the fields that are being hidden, and if they are controllers themselves,
                            //toggle them off and hide their controlled fields
                            if ($thisControlled.length) {
                                $thisControlled.each(function(i, value) {
                                    let $c = $(this).find('.controller');
                                    if ($c.length) {
                                        //console.info('One of the fields you are hiding is a controller, so hide its fields!');
                                        auPluginAdminDashboard.forms.toggleCheckbox($c, false);
                                        auPluginAdminDashboard.forms.controlledFields.toggleControlledFields($c.attr('id'), false);
                                    }
                                });
                            }
                        }
                    });
                } else {
                    let toggle = forcedToggle === null || forcedToggle === undefined ? auPluginAdminDashboard.forms.getCheckbox($controller) : forcedToggle;
                    if (toggle) {
                        //Checkbox or radio button is true, so reveal its options
                        $controlled.removeClass('hide');
                        //If there are any required fields, add the required flag to them
                        let $required_fields = $controlled.find('[data-required="true"]');
                        if ($required_fields.length > 0) {
                            $required_fields.each(function() {
                                $(this).attr('required', 'required');
                            });
                        }
                        if ($controller.is('[type=radio]')) {
                            //Because we are a radio button, we have to hide all other options except for this
                            let $radioGroup = $('[name="' + $controller.attr('name') + '"]:not(#' + id + ')');
                            //Search through the fields that are being hidden, and if they are controllers themselves,
                            //toggle them off and hide their controlled fields
                            if ($radioGroup.length) {
                                $radioGroup.each(function(i, value) {
                                    auPluginAdminDashboard.forms.controlledFields.toggleControlledFields($(this).attr('id'), false);
                                });
                            }
                        }
                    } else {
                        //Checkbox or radio button is false, so hide its options
                        $controlled.addClass('hide');
                        //If there are any required fields, remove the required flag from them
                        let $required_fields = $controlled.find('[required]');
                        if ($required_fields.length > 0) {
                            $required_fields.each(function() {
                                $(this).removeAttr('required');
                            });
                        }
                        //Search through the fields that are being hidden, and if they are controllers themselves,
                        //toggle them off and hide their controlled fields
                        if ($controlled.length) {
                            $controlled.each(function(i, value) {
                                let $c = $(this).find('.controller');
                                if ($c.length) {
                                    //console.info('One of the fields you are hiding is a controller, so hide its fields!');
                                    auPluginAdminDashboard.forms.toggleCheckbox($c, false);
                                    auPluginAdminDashboard.forms.controlledFields.toggleControlledFields($c.attr('id'), false);
                                }
                            });
                        }
                    }
                }
            }
        }
    }, // End of `forms`
    accordion: {
        init: function() {
            //jQuery Accordion Documentation: https://api.jqueryui.com/accordion/
            var $accordions = $('.au-accordion');
            if ($accordions.length) {
                try {
                    $accordions.each(function() {
                        var $a = $(this); //This accordion
                        $a.accordion({
                            collapsible: true, //Allow all panels to be collapsed
                            header: '.au-accordion-item-title',
                            heightStyle: 'content' //Each panel will only be as tall as its content
                        });
                    });
                    //console.log('Accordions initialised!');
                } catch (ex) {
                    console.error('Error initialisting accordions!', ex);
                }
            }
        }
    }, // End of `accordion`
    updateHours: {
        start: function(e) {
            e.preventDefault();
            auPluginAdminDashboard.updateHours.toggleFormStatus(true); //Disable the form
            $('.au-plugin form#crontrol-hours-update .form-response-output').html('').addClass('hide'); //Clear out and hide old response
            const form_data = $(this).serialize();
            console.info('Submitting form data', form_data);
            //$('#generate-status').attr('class', 'status-text notice notice-info hide');
            setTimeout(function() {
                $.ajax({
                    type: 'POST',
                    url: au_dashboard.ajax_url,
                    data: {
                        'action': 'crontrol_hours_update', //name of handle after "wp_ajax_" prefix
                        'fields': form_data
                    },
                    cache: false,
                    error: function(xhr) {
                        //console.error('AJAX Error', xhr);
                        auPluginAdminDashboard.updateHours.complete({
                            'success': 0,
                            'error': xhr,
                            'output': xhr.responseText + ' Error: Ajax Error'
                        });
                    },
                    success: function(response) {
                        //console.info('AJAX Success', response);
                        try {
                            response = JSON.parse(response);
                            setTimeout(function() {
                                auPluginAdminDashboard.updateHours.complete(response);
                            }, 500);
                        } catch (xhr) {
                            auPluginAdminDashboard.updateHours.complete({
                                'success': 0,
                                'error': xhr,
                                'response': response,
                                'output': xhr.responseText + ' Error: JSON Error'
                            });
                        }
                    }
                });
            }, 50);
        },
        toggleFormStatus(toggle) {
            let $form = $('.au-plugin form#crontrol-hours-update'),
                $btn = $('.au-plugin form#crontrol-hours-update [type="submit"]'),
                $spinner = $('.au-plugin form#crontrol-hours-update .progress-spinner');
            if (toggle) {
                //Disable the form
                $form.attr('disabled', 'disabled');
                $btn.attr('disabled', 'disabled');
                $spinner.removeClass('hide');
            } else {
                //Enable the form
                $form.removeAttr('disabled');
                $btn.removeAttr('disabled');
                $spinner.addClass('hide');
            }
        },
        complete(response) {
            //console.log('Update CRON Events complete', response);
            auPluginAdminDashboard.updateHours.toggleFormStatus(false); //Enable the form
            if (response.output) {
                $('.au-plugin form#crontrol-hours-update .form-response-output').html(response.output).removeClass('hide');
            }
        }
    } // End of `updateHours`
}; // End of `auPluginAdminDashboard`
$(document).ready(auPluginAdminDashboard.init);