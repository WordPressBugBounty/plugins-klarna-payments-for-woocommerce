jQuery(function ($) {
    const krokedil_styled_settings = {
        originalSubmitPlacement: null,
        /**
         * Toggles the Krokedil settings sections.
         */
        toggleSettingsSection: function () {
            $('.krokedil_settings__section_header').on('click', function () {
                krokedil_styled_settings.toggleSectionContent($(this));
            });
        },

       /**
         * Moves the submit button to a new placement or restores it.
         */
        moveSubmitButton: function () {
            let $submitBtn = $('.krokedil_settings__gateway_page p.submit');
            let $newSubmitPlacement = $('.krokedil_settings__gateway_page.styled');

            if(!krokedil_styled_settings.originalSubmitPlacement) {
                krokedil_styled_settings.originalSubmitPlacement = $submitBtn.parent();
            }

            if (window.innerWidth >= 660) {
                if ($newSubmitPlacement.length && $submitBtn.length && !$newSubmitPlacement.find($submitBtn).length) {
                    $newSubmitPlacement.append($submitBtn);
                }
            } else {
                krokedil_styled_settings.originalSubmitPlacement.append($submitBtn);
            }
        },

        /**
         * Smooth scrolls to anchor links.
         */
        smoothScroll: function () {
            $(document).on('click', 'a.krokedil_settings__settings_navigation_link', function (event) {
                event.preventDefault();
                let $section = $('#krokedil_section_' + $(this).attr('href').replace('#', ''));

                if(!$section.length) {
                    return;
                }

                history.replaceState(null, null, $(this).attr('href'));

                if (!$section.find('.krokedil_settings__section_content').hasClass('active')) {
                    krokedil_styled_settings.toggleSectionContent($section);
                }

                $('html, body').animate({
                    scrollTop: $section.offset().top - 100
                }, 500);
            });
        },

        /**
         * Toggles the content of the settings section.
         */
        toggleSectionContent: function ($section) {
            $section.find('.krokedil_settings__section_toggle')
                    .toggleClass('dashicons-arrow-up-alt2')
                    .toggleClass('dashicons-arrow-down-alt2');

            let $sectionContent = $section.closest('.krokedil_settings__section').find('.krokedil_settings__section_content');
            $sectionContent.toggleClass('active');
        },

        /**
         * Opens the settings section based on the URL hash.
         */
        openSettingsSection: function () {
            let sectionId = window.location.hash ?? '';
            let $section = $('#krokedil_section_' + sectionId.replace('#', ''));

            if ($section.length) {
                krokedil_styled_settings.toggleSectionContent($section);
            }
        },

        conditionalSettings: function () {
            $conditionalTogglers = $('.krokedil_conditional_toggler');

            if ($conditionalTogglers.length) {
                $conditionalTogglers.each(function () {
                    krokedil_styled_settings.toggleConditionalSettings($(this));
                });
            }
        },

        toggleConditionalSettings: function ($togglerSetting) {
            const conditionalTarget = $togglerSetting.attr('class').match(/krokedil_toggler_([^\s]+)/)?.[1];

            if (!conditionalTarget) {
                return;
            }

            const toggleableOptions = $togglerSetting.attr('class').split(' ').filter(c => c.startsWith('toggler_option_'));
            const isToggleableVal = toggleableOptions.includes('toggler_option_' + $togglerSetting.val());
            const enabled = $togglerSetting.is('select') ? isToggleableVal : $togglerSetting.is(':checked');
            

            let $conditionalSettings = $('.krokedil_conditional_' + conditionalTarget).closest('tr');

            $conditionalSettings.toggle(enabled);

            // Check any of the conditional settings has its own toggle.
            if($conditionalSettings.find('.krokedil_conditional_toggler').length) {
                const $conditionalSettingsTogglers = $conditionalSettings.filter(function() {
                    return $(this).find('.krokedil_conditional_toggler').length > 0;
                });

                krokedil_styled_settings.toggleNestedConditionalSettings($conditionalSettingsTogglers, enabled);
            }
        },

        /** 
        * Check if there are any nested conditional togglers inside the conditional settings.
        * They may need toggling depending on the parent. 
        */
        toggleNestedConditionalSettings: function ($conditionalSettingsTogglers, enabled) {
            $conditionalSettingsTogglers.each(function () {
                const $conditionalSetting = $(this).find('.krokedil_conditional_toggler');

                if(!$conditionalSetting.length) {
                    return;
                }

                const toggleableOptions = $conditionalSetting.attr('class').split(' ').filter(c => c.startsWith('toggler_option_'));
                const isToggleableVal = toggleableOptions.includes('toggler_option_' + $conditionalSetting.val());
                const nestedEnabled = $conditionalSetting.is('select') ? isToggleableVal : $conditionalSetting.is(':checked') && enabled;

                const conditionalTarget = $conditionalSetting.attr('class').match(/krokedil_toggler_([^\s]+)/)?.[1];

                if (!conditionalTarget) {
                    return;
                }

                let $conditionalSettingsTogglers = $('.krokedil_conditional_' + conditionalTarget).closest('tr');

                $conditionalSettingsTogglers.toggle(nestedEnabled);
            });
        },

        upsellSettings: function () {
             $('.krokedil_ppu_setting').closest('tr').addClass('krokedil_ppu_status');


            const upsellPluginIsActive = $('.krokedil_ppu_setting').hasClass('active');
            $('.krokedil_ppu_setting').closest('tr').toggleClass('active', upsellPluginIsActive);
            $('.krokedil_ppu_setting__title').next('p').toggle(!upsellPluginIsActive);
        },

        /**
         * Initializes the events for this file.
         */
        init: function () {
            if ( ! $('.krokedil_settings__gateway_page.styled').length ) {
                return;
            }

            $(document)
                .ready(this.toggleSettingsSection)
                .ready(this.moveSubmitButton)
                .ready(this.smoothScroll)
                .ready(this.openSettingsSection)
                .ready(this.conditionalSettings)
                .ready(this.upsellSettings);

            $(window).on('resize', this.moveSubmitButton);
            
            $(document).on('change', '.krokedil_conditional_toggler', function() {
                krokedil_styled_settings.toggleConditionalSettings($(this));
            });
        },
    };
    krokedil_styled_settings.init();
});