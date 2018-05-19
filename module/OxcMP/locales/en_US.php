<?php

// Use snake_case for keys
return [
    // Meta
    '' => ['plural_forms' => 'nplurals=2; plural=(n==1 ? 0 : 1)'],
    // Global
    'global_application_name' => 'OpenXcom Mod Portal',
    'global_application_description' => 'Portal description in a few words, with some feature highlights, or portal slogan?',
    'global_close' => 'Close',
    'global_bad_request' => 'Bad request.',
    'global_unexpected_error' => 'Unexpected error.',
    'global_chunk_upload_unsupported' => 'Outdated browser, please upgrade.',
    // Main menu
    'main_menu_nav_toggle' => 'Toggle navigation',
    'main_menu_link_home_txt' => 'Home',
    'main_menu_link_mymods_txt' => 'My Mods',
    'main_menu_link_logout_txt' => 'Logout',
    'main_menu_link_login_txt' => 'Login',
    // Footer
    'footer_nav_toggle' => 'Toggle navigation',
    'footer_link_about_txt' => 'About',
    'footer_link_disclaimer_txt' => 'Disclaimer',
    'footer_link_contact_txt' => 'Contact',
    'footer_link_github_title' => 'Visit our GitHub project page!',
    // Login
    'login_modal_title' => 'Log in',
    'login_modal_description' => 'To login, you must authorize the OpenXcom Mod Portal to use your OpenXcom Forum member details.',
    'login_modal_oauth_link_txt' => 'Go to the OpenXcom Forum authorization page',
    'login_success_message' => 'Welcome <strong>%s</strong>, you are now logged in.',
    'login_fail_message' => '<strong>Authorization failed</strong>, please try again later.',
    'login_logout_message' => 'You have successfully logged out.',
    // ACL
    'acl_not_logged_in' => 'You are not logged in.',
    'acl_not_allowed' => 'Access denied.',
    // Module bootstrap
    'module_bootstrap_usercheck_board_in_maintenance' => 'You have been logged out - The OpenXcom forum is in maintenance mode.',
    'module_bootstrap_usercheck_member_deleted' => 'You have been logged out - Your user was deleted from the OpenXcom forum.',
    'module_bootstrap_usercheck_member_banned' => 'You have been logged out - Your user was banned from the OpenXcom forum.',
    'module_bootstrap_usercheck_invalid_auth_token' => 'You have been logged out - Please re-authenticate.',
    'module_bootstrap_usercheck_invalid_api_key' => 'You have been logged out due to an internal error.',
    // My Mods page
    'page_mymods_title' => 'Manage your mods',
    'page_mymods_description_no_mods' => 'You have not created any mods',
    'page_mymods_description_ony_published_mods' => 'You have created %s mod(s)',
    'page_mymods_description_ony_unpublished_mods' => 'You have created %s mod(s), all of which are unpublished',
    'page_mymods_description_published_and_unpublished_mods' => 'You have created %s mod(s), out of which %s mod(s) are unpublished',
    'page_mymods_card_new_mod_title' => 'Create new mod',
    'page_mymods_card_new_mod_description' => 'Showcase your creativity',
    'page_mymods_card_new_btn_create' => 'Create',
    'page_mymods_create_modal_title' => 'Create a new mod',
    'page_mymods_create_modal_mod_title_title' => 'Type a title for the mod:',
    'page_mymods_create_modal_mod_title_placeholder' => 'Mod title',
    'page_mymods_create_modal_mod_title_help' => 'Must contain 4 to 64 Latin letters, numbers and basic punctuation.',
    'page_mymods_create_modal_submit' => 'Create mod',
    'page_mymods_create_error_title_length_short' => 'Title too short.',
    'page_mymods_create_error_title_length_long' => 'Title too long.',
    'page_mymods_create_error_title_characters_forbidden' => 'Title contains forbidden characters.',
    'page_mymods_create_error_unknown' => 'System error, please try again later.',
    // Edit Mod Page
    'page_editmod_title' => 'Edit mod',
    'page_editmod_description' => 'Make changes to your mod',
    'page_editmod_image_background_alt' => 'Mod background image',
    'page_editmod_image_background_process_alt' => 'Background image upload process',
    'page_editmod_mod_not_found' => 'The specified mod could not be found.',
    'page_editmod_form_title_title' => 'Title:',
    'page_editmod_form_title_placeholder' => 'Mod title',
    'page_editmod_form_title_help' => 'Must contain 4 to 64 Latin letters, numbers and basic punctuation.',
    'page_editmod_error_title_length_short' => 'Title too short.',
    'page_editmod_error_title_length_long' => 'Title too long.',
    'page_editmod_error_title_characters_forbidden' => 'Title contains forbidden characters.',
    'page_editmod_form_slug_title' => 'Slug:',
    'page_editmod_form_slug_placeholder' => '<unavailable>',
    'page_editmod_form_slug_help' => 'Generated automatically from the mod title.',
    'page_editmod_form_published_title' => 'Status:',
    'page_editmod_form_published_yes' => 'Published',
    'page_editmod_form_published_no' => 'Unpublished',
    'page_editmod_form_published_help' => 'Unpublished mods are not listed on the public pages.',
    'page_editmod_form_summary_title' => 'Summary:',
    'page_editmod_form_summary_placeholder' => 'Mod description, short version',
    'page_editmod_form_summary_help' => 'One-line summary, must contain 4 to 128 Latin letters, numbers and basic punctuation.',
    'page_editmod_error_summary_length_short' => 'Summary is too short.',
    'page_editmod_error_summary_length_long' => 'Summary is too long.',
    'page_editmod_error_summary_characters_forbidden' => 'The summary contains forbidden characters.',
    'page_editmod_form_description_title' => 'Description:',
    'page_editmod_form_description_edit' => 'Edit',
    'page_editmod_form_description_preview' => 'Preview',
    'page_editmod_form_description_placeholder' => 'Complete mod description',
    'page_editmod_form_description_help' => 'Must contain 4 to 65,535 Latin letters, numbers, basic punctuation and <a href="%s" target="_blank">GitHub Flavored Markdown Syntax</a> elements. Some HTML is allowed.',
    'page_editmod_error_description_length_short' => 'Description is too short.',
    'page_editmod_error_description_length_long' => 'Description is too long.',
    'page_editmod_error_description_characters_forbidden' => 'The description contains forbidden characters.',
    'page_editmod_error_bad_request' => 'The description contains forbidden characters.',
    'page_editmod_error_unknown' => 'System error, please try again later.',
    'page_editmod_form_tags_selected_title' => 'Mod tags:',
    'page_editmod_form_tags_selected_none' => 'None',
    'page_editmod_form_tags_selected_help' => 'Click or touch a tag to remove it.',
    'page_editmod_form_tags_search_title' => 'Add new tag:',
    'page_editmod_form_tags_search_placeholder' => 'Search for tags',
    'page_editmod_form_tags_search_help' => 'Type to search, click or touch a tag to add it.',
    'page_editmod_form_background_current_title' => 'Mod page background image:',
    'page_editmod_form_background_manage_title' => 'Manage background image:',
    'page_editmod_form_background_manage_help_intro' => 'How the background upload process works:',
    'page_editmod_form_background_manage_help_1' => 'Upload a clean image.',
    'page_editmod_form_background_manage_help_2' => 'Gradient is added automatically.',
    'page_editmod_form_background_manage_help_3' => 'The result is used on the mod page.',
    'page_editmod_form_background_manage_help_technical' => 'Image must be exactly %sx%s pixels. Most image formats are supported. Maximum file size is %s MB. <a href="%s">Download sample overlay and gradient images</a>.',
    'page_editmod_form_background_manage_btn_upload' => 'Upload',
    'page_editmod_form_background_manage_btn_default' => 'Default',
    'page_editmod_error_storage_insufficient' => 'Insufficient storage space.',
    'page_editmod_error_storage_user_quota' => 'User quota reached.',
    'page_editmod_error_storage_mod_quota' => 'Mod quota reached.',
    'page_editmod_error_upload_too_big' => 'File is too big.',
    'page_editmod_error_upload_unavailable' => 'File uploads unavailable.',
    'page_editmod_error_file_not_resource' => 'Not a zip file',
    'page_editmod_error_file_not_image' => 'Not an image file.',
    'page_editmod_error_file_not_background' => 'Incorrect image dimensions.',
    'page_editmod_success_background' => 'Background uploaded.',
    'page_editmod_background_default' => 'Use the default background?',
    'page_editmod_background_default_help' => 'This will remove the personalized background set for this mod.',
    'page_editmod_background_default_use' => 'Use default',
    'page_editmod_success_background_default' => 'Default background restored.',
    'page_editmod_form_gallery_title' => 'Image gallery:',
    'page_editmod_form_gallery_help' => 'Most image formats are supported. May select multiple images for upload.  Maximum file size is %s MB. First image is used as cover.',
    'page_editmod_form_gallery_add' => 'Add images',
    'page_editmod_form_gallery_edit' => 'Edit',
    'page_editmod_form_upload_modal_title' => 'Uploading <span>X</span> file(s)...',
    'page_editmod_form_upload_modal_abort' => 'Abort',
    'page_editmod_error_file_upload_abort' => 'Upload aborted.',
    'page_editmod_error_file_upload_retry' => 'Upload failed.',
    'page_editmod_error_multi_upload_abort' => 'Upload aborted, X file(s) uploaded.',
    'page_editmod_error_multi_upload_failure' => 'X file(s) uploaded, X file(s) failed.',
    'page_editmod_success_multi_upload' => 'Successfully uploaded X file(s).',
    'page_editmod_form_resources_title' => 'Files',
    'page_editmod_form_resources_help' => 'ZIP format only. May select multiple images for upload.  Maximum file size is %s MB.',
    'page_editmod_form_resources_add' => 'Add files',
    'page_editmod_form_file_caption' => 'Caption:',
    'page_editmod_form_file_caption_placeholder' => 'No caption',
    'page_editmod_form_file_caption_help' => 'May contain up to 128 Latin letters, numbers and basic punctuation.',
    'page_editmod_form_file_filename' => 'Filename:',
    'page_editmod_form_file_filename_placeholder' => 'Auto-generated filename',
    'page_editmod_form_file_filename_help' => 'May contain up to 128 lowercase latin letters, numbers and dashes.',
    'page_editmod_form_file_order' => 'Position:',
    'page_editmod_form_file_order_placeholder' => 'Position in list',
    'page_editmod_form_file_order_help' => 'Position in list, starting from 1. Will push back other file(s) if needed.',
    'page_editmod_form_edit_file_modal_title_image' => 'Edit image',
    'page_editmod_form_edit_file_modal_title_resource' => 'Edit file',
    'page_editmod_form_edit_file_modal_update' => 'Update',
    'page_editmod_form_edit_file_modal_delete' => 'Delete',
    'page_editmod_form_edit_file_modal_delete_confirm' => 'Yes, delete it',
    'page_editmod_form_edit_file_modal_delete_cancel' => 'Cancel',
    'page_editmod_error_invalid_description' => 'Invalid caption',
    'page_editmod_error_invalid_filename' => 'Invalid filename',
    'page_editmod_error_invalid_position' => 'Invalid position',
    // Mod edit messages
    'page_editmod_success' => 'Mod succesfully updated.',
    'page_editmod_form_btn_submit' => 'Update',
    'page_editmod_form_btn_delete' => 'Delete',
];

/* EOF */