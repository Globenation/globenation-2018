/**
 * used in wp-admin -> edit page, not on posts
 * this class hides and shows the metaboxes acording to the selected template
 * @type {{init: Function, show_template_settings: Function, change_content: Function}}
 */
var td_edit_page = {

    init: function () {
        jQuery().ready(function() {
            var td_page_metabox = jQuery('#td_page_metabox');

            // #td_page_metabox is removed when td composer is loaded. But it's not removed from those iframes of td composer which usually load backend settings (ex iframes with page settings)
            if (td_page_metabox.length) {

                var td_homepage_loop_metabox = jQuery( '#td_homepage_loop_metabox' );

                //hide boxes - avoid displaying both at the same time, a class is used to avoid interference with "Screen Options" settings
                td_page_metabox.addClass('td-hide-metabox');
                td_homepage_loop_metabox.addClass('td-hide-metabox');
                setTimeout(function () {
                    td_edit_page.show_template_settings();

                    jQuery('#page_template').change(function () {
                        td_edit_page.show_template_settings();
                    });

                }, 200);

                //disable sidebar settings - if any vc_row is present in the page content
                setInterval(function () {

                    // Disable meta box section when composer is active
                    if ('undefined' !== typeof window.parent.tdcPostSettings && 'undefined' !== typeof window.parent.tdcPostSettings.postContent ) {
                        td_page_metabox.addClass('td-disable-settings');
                        return;
                    }

                    var vcRows = jQuery('#content_ifr').contents().find('#tinymce').text().match(/\[.*vc_row.*\]/m);
                    if (vcRows !== null) {
                        td_page_metabox.addClass('td-disable-settings');
                    } else {
                        td_page_metabox.removeClass('td-disable-settings');
                    }
                }, 500);
            }

        });

    },


    show_template_settings: function () {
        if (jQuery('#post_type').val() == 'post') {
            return;
        }


        //text and image after template drop down
        td_edit_page.change_content();

        var cur_template = jQuery('#page_template option:selected').text(),
            td_page_metabox = jQuery('#td_page_metabox'),
            td_homepage_loop_metabox = jQuery('#td_homepage_loop_metabox');

        // the "show only unique articles" box is always visible
        // "postbox" class is removed for hidden elements to reduce the flickering occurred while dragging a metabox to change it's position
        switch (cur_template) {
            case 'Pagebuilder + latest articles + pagination':
                //hide default page settings
                td_page_metabox.removeClass('postbox');
                td_page_metabox.addClass('td-hide-metabox');
                //display homepage loop settings
                td_homepage_loop_metabox.addClass('postbox');
                td_homepage_loop_metabox.removeClass('td-hide-metabox');
                td_edit_page.change_content('<span class="td-wpa-info"><strong>Tip:</strong> Homepage made from a pagebuilder section and a loop below. <ul><li>The loop supports an optional sidebar and advanced filtering options. </li> <li>You can find all the options of this template if you scroll down.</li></ul></span>');
                break;

            case 'Pagebuilder + page title':
                //hide homepage loop settings
                td_homepage_loop_metabox.addClass('td-hide-metabox');
                td_homepage_loop_metabox.removeClass('postbox');
                //display default page settings
                td_page_metabox.addClass('postbox');
                td_page_metabox.removeClass('td-hide-metabox');
                td_edit_page.change_content('<span class="td-wpa-info"><strong>Tip:</strong> Useful when you want to create a page that has a standard title using the page builder. <ul><li>This template will remove the sidebar when a Visual Composer or other composers are used.</li> <li>Use the  Widgetised Sidebar block to add a sidebar.</li></ul>');
                break;

            default: //default template
                //hide homepage loop settings
                td_homepage_loop_metabox.addClass('td-hide-metabox');
                td_homepage_loop_metabox.removeClass('postbox');
                //display default page settings
                td_page_metabox.addClass('postbox');
                td_page_metabox.removeClass('td-hide-metabox');
                td_edit_page.change_content('<span class="td-wpa-info"><strong>Tip:</strong> Default template, perfect for <em>page builder</em> or content pages. <ul><li>If the page builder is used, the page will be without a title.</li> <li>If it\'s a content page the template will generate a title</li></ul></span>');
                break;
        }
    },


    change_content: function (the_text) {
        if(document.getElementById("td_after_template_container_id")) {
            var after_element = document.getElementById("td_after_template_container_id");
            after_element.innerHTML = "";
            if(typeof the_text != 'undefined') {
                after_element.innerHTML = the_text;
            }
        } else {
            if(document.getElementById("page_template")) {
                //create the container
                var after_element = document.createElement("div");
                after_element.setAttribute("id", "td_after_template_container_id");
                //insert the element in DOM, after template pull down
                document.getElementById("page_template").parentNode.insertBefore(after_element, document.getElementById("page_template").nextSibling);
            }
        }
    }
};

td_edit_page.init();
