/* 
 * Copyright © 2016-2017 OpenXcom Mod Portal Developers
 *
 * This file is part of OpenXcom Mod Portal.
 *
 * OpenXcom Mod Portal is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OpenXcom Mod Portal is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OpenXcom Mod Portal. If not, see <http://www.gnu.org/licenses/>.
 */

class EditModManager {
    /**
     * Setup mod editing
     * 
     * @param {TagManager} tagManager The tag manager
     * @returns {EditModManager}
     */
    constructor(tagManager) {
        this.tagManager = tagManager;
        /*
         * A list of newly uploaded mod files and their properties, indexed by their UUID
         * this.createdFiles = {
         *      '4078c7b2-fdc2-46eb-92c6-2fce2ebffcdd': {
         *          type: 'image',
         *          name: 'galleryPhoto1',
         *          description: 'Some description'
         *      }
         * }
         */
        this.createdFiles = {};
        
        /*
         * A list of updated mod mod files and their properties, indexed by their UUID
         * this.createdFiles = {
         *      '4078c7b2-fdc2-46eb-92c6-2fce2ebffcdd': {
         *          name: 'galleryPhoto1',
         *          description: 'Some description'
         *      }
         * }
         */
        this.updatedFiles = {};
        
        /*
         * A list of deleted files UUIDs (newly uploaded and deleted files are listed too,
         * as this means less processing on the frontend)
         */
        this.deletedFiles = [];

        // Delay before making the slug preview request, in milliseconds
        this.modSlugPreviewDelay = 500;
        
        this.$editModForm = $('form#editMod');
        this.modSlugPreviewTimer = null;
        
        // Customizations
        this.beautifyBtnGroup();
        this.$editModForm.submit(this.handleSubmit);
        
        // Set the slug preview timer on every keyup event
        $('input#title', this.$editModForm).keyup(this.setSlugPreviewTimer);
        
        // Preview mod description on preview tab focus, if needed
        this.wasDescriptionChanged = false;
        this.wasDescriptionPreviewStarted = false;
        $('textarea#descriptionRaw', this.$editModForm).change(function(){editModManager.wasDescriptionChanged = true;});
        $('a[data-toggle="tab"]', this.$editModForm).on('shown.bs.tab', this.previewModDescription);
    }
    
    /**
     * Set the slug preview timer
     * 
     * @param {event] event The event
     * @returns {undefined}
     */
    setSlugPreviewTimer(event) {
        clearTimeout(editModManager.modSlugPreviewTimer);
        editModManager.modSlugPreviewTimer = setTimeout(
            editModManager.previewModSlug.bind(null, event),
            editModManager.modSlugPreviewDelay
        );
    }
    
    /**
     * Preview the mod slug based on the title
     * 
     * @param {event] event The event
     * @returns {undefined}
     */
    previewModSlug(event) {
        $.ajax(editModManager.$editModForm.data('slug-preview-action'), {
            method: 'post',
            data: {
                id: $('input#id', editModManager.$editModForm).val(),
                title: $(event.target).val()
            },
            dataType: 'json'
        })
        .done(function(data){
            $('input#slug', editModManager.$editModForm).val(data.slug);
        });
    }
    
    /**
     * Make the button group look nicer
     * 
     * @returns {undefined}
     */
    beautifyBtnGroup() {
        $('div.btn-group', this.$editModForm).each(function(){
            var $btnGroup = $(this);
            var $labels = $('label', $btnGroup);

            var activeClass = $btnGroup.data('active-class');
            var inactiveClass = $btnGroup.data('inactive-class');
            
            $('input', $btnGroup).change(function(){
                $labels.removeClass(activeClass).addClass(inactiveClass);
                $(this).parent().removeClass(inactiveClass).addClass(activeClass);
            });
        });
    }
    
    /**
     * Preview the mod description, if something was changed
     * 
     * @param {event} event The event
     * @returns {undefined}
     */
    previewModDescription(event) {
        var target = $(event.target);
        
        // This is not the tab we're looking for, move along
        if (target.attr('id') !== 'description-preview-tab') {
            return;
        }
        
        // Sync div height when switching to preview
        // TODO: Use another option, like a resizable tab container and CSS flex, this is hackish
        $('div#description-preview', editModManager.$editModForm).height(
            $('div#description-edit', editModManager.$editModForm).height()
        );
        
        // Don't make a request if nothing was changed by the user
        // or if there is a request already in progress
        if (
            editModManager.wasDescriptionChanged === false
            || editModManager.wasDescriptionPreviewStarted === true
        ) {
            return;
        }
        
        // Prevent other requests until this one is done
        editModManager.wasDescriptionPreviewStarted = true;
        
        // Show the loading hint, hide the description
        $('div#description-preview-progress', editModManager.$editModForm).removeClass('d-none');
        $('div#description-preview-content', editModManager.$editModForm).addClass('d-none');
        
        $.ajax(editModManager.$editModForm.data('description-preview-action'), {
            method: 'post',
            data: {
                id: $('input#id', editModManager.$editModForm).val(),
                descriptionRaw: $('textarea#descriptionRaw', editModManager.$editModForm).val()
            },
            dataType: 'json'
        })
        .done(function(data){
            // The result goes into the preview regardless of the success status
            $('div#description-preview-content', editModManager.$editModForm).html(data.content);
            editModManager.wasDescriptionChanged = false;
        })
        .fail(function(){
            $('div#description-preview-content', editModManager.$editModForm).html('There was an error fetrching the preview, please try again later...');
        })
        .always(function(){
            $('div#description-preview-progress', editModManager.$editModForm).addClass('d-none');
            $('div#description-preview-content', editModManager.$editModForm).removeClass('d-none');
            
            editModManager.wasDescriptionPreviewStarted = false;
        });
    }
    
    /**
     * Handle form submission
     * 
     * @param {event} event The event
     * @returns {undefined}
     */
    handleSubmit(event) {
        event.preventDefault();
        editModManager.setLoadingState(true);
        
        // Collect
        
        $.ajax(editModManager.$editModForm.attr('action'), {
            method: 'post',
            data: {
                id: $('input#id', editModManager.$editModForm).val(),
                title: $('input#title', editModManager.$editModForm).val(),
                isPublished: $('input[name=isPublished]:checked', editModManager.$editModForm).val(),
                summary:  $('textarea#summary', editModManager.$editModForm).val(),
                descriptionRaw:  $('textarea#descriptionRaw', editModManager.$editModForm).val(),
                tags: editModManager.tagManager.selectedTags.join(',')
            },
            dataType: 'json'
        })
        .done(editModManager.handleSubmitDone)
        .fail(editModManager.handleSubmitFail);
    }
    
    /**
     * Handle form success submission result
     * 
     * @param {jqXHR}  data       The received response
     * @param {string} textStatus Textual status message
     * @param {jqXHR}  jqXHR      The jqXHR object
     * @returns {undefined}
     */
    handleSubmitDone(data, textStatus, jqXHR) {
        if (data.success) {
            window.location.href = data.content;
            return;
        }
        
        editModManager.setLoadingState(false);
        $('div#error-message', editModManager.$editModForm).removeClass('d-none').text(data.content);
    }
    
    /**
     * Handle form failed submission
     * 
     * @param {jqXHR}   jqXHR       The jqXHR object
     * @param {string}  textStatus  Textual status message
     * @param {integer} errorThrown Error thrown
     * @returns {undefined}
     */
    handleSubmitFail(jqXHR, textStatus, errorThrown) {
        editModManager.setLoadingState(false);
        alert('Unexpected error, please try again');
    }
    
    /**
     * Set the loading state for the edit mod form
     * 
     * @param {boolean} state The state
     * @returns {undefined}
     */
    setLoadingState(state) {
        // TODO: Disable form controls according to the state
        if (state) {
            $('div#error-message', editModManager.$editModForm).addClass('d-none');
            $('button', editModManager.$editModForm).attr('disabled','');
            $('div#progress', editModManager.$editModForm).removeClass('d-none');
        } else {
            $('button', editModManager.$editModForm).removeAttr('disabled');
            $('div#progress', editModManager.$editModForm).addClass('d-none');
        }
    }
    

}

class TagManager {
    /**
     * Setup tag editing
     * 
     * @returns {TagManager}
     */
    constructor () {
        // Form elements
        this.$editModForm = $('form#editMod');
        this.$tagSelect = $('div#tag-select', this.$editModForm);
        this.$tagSelectNone = $('div#tag-select-none', this.$editModForm);
        this.$tagSearch = $('input#tag-search', this.$editModForm);
        this.$tagSearchResultContainer = $('div#tag-search-result', this.$editModForm)
        this.$tagSearchResultPanel = $('div', this.$tagSearchResultContainer)
        
        this.selectedTags = this.$tagSelect.data('selected').length !== 0
            ? this.$tagSelect.data('selected').split(',')
            : [];
        this.availableTags = this.$tagSearch.data('available').split(',');
            
        this.searchResult = [];
        
        // Don't submit the form on enter when searching for tags
        this.$tagSearch.keypress(function(event){
            if (event.which === 13) {
                event.preventDefault();
            }
        });
        
        this.$tagSearch.keyup(this.searchTag);
        
        // Render inital tags
        this.renderTagSelection(this);
    }
    
    /**
     * Search for tags matching the entered text
     * 
     * @param {event} event The event
     * @returns {Boolean}
     */
    searchTag(event) {
        // On Enter, add the first tag in the results list (if any) to the selected tags.
        if (event.which === 13) {
           console.log('auto add');
           return;
        }
        
        var searchTerm = tagManager.$tagSearch.val().trim();
        tagManager.$tagSearch.val(searchTerm);
        // Search for matching tags
        var searchResult = [];

        if (searchTerm.length !== 0) {
            tagManager.availableTags.forEach(function(tag){
                if (
                    (tag.includes(searchTerm) === true || searchTerm === '*')
                    && tagManager.selectedTags.indexOf(tag) === -1
                ) {
                    searchResult.push(tag);
                }
            });
        }
        
        if (tagManager.searchResult.toString() === searchResult.toString()) {
            return;
        }

        tagManager.searchResult = searchResult;
        
        tagManager.renderTagSearch();
    }
    
    /**
     * Render the searched tags on the form
     * 
     * @returns {undefined}
     */
    renderTagSearch () {
        if (tagManager.searchResult.length === 0) {
            tagManager.$tagSearchResultContainer.addClass('d-none');
            return;
        }
        
        tagManager.$tagSearchResultPanel.empty();
        tagManager.$tagSearchResultContainer.removeClass('d-none');

        tagManager.searchResult.forEach(function(tag){
            var $tag = $('<a />', {href: '#', class: 'badge badge-primary p-2 mr-2 mb-2'});
            $tag.text(tag);
            $tag.click(tagManager.selectTag);
            tagManager.$tagSearchResultPanel.append($tag);
        });
    }
    
    /**
     * Render the selected tags on the form
     * 
     * @param {TagManager} tm The tag manager
     * @returns {undefined}
     */
    renderTagSelection (tm) {
        if (tm.selectedTags.length === 0) {
            tm.$tagSelect.addClass('d-none');
            tm.$tagSelectNone.removeClass('d-none');
            return;
        }
        
        tm.$tagSelect.removeClass('d-none').empty();
        tm.$tagSelectNone.addClass('d-none');
        
        tm.selectedTags.forEach(function(tag){
            var $tag = $('<a />', {href: '#', class: 'badge badge-primary p-2 mr-2 mb-2'});
            $tag.text(tag);
            $tag.click(tm.removeTag);
            tm.$tagSelect.append($tag);
        });
    }
    
    /**
     * Add a tag to the selected tags list
     * 
     * @param {event} event The event
     * @returns {undefined}
     */
    selectTag(event) {
        event.preventDefault();
        var $tag = $(event.target);
        
        tagManager.selectedTags.push($tag.text());
        tagManager.selectedTags.sort();
        tagManager.$tagSearch.val('').keyup();
        tagManager.renderTagSelection(tagManager);
    }
    
    /**
     * Remove the tag from the selected tags list
     * 
     * @param {type} event The event
     * @returns {undefined}
     */
    removeTag (event) {
        event.preventDefault();
        var $tag = $(event.target);
        
        var index = tagManager.selectedTags.indexOf($tag.text());
        tagManager.selectedTags.splice(index, 1);
        
        // Update the search panel, if open
        tagManager.renderTagSelection(tagManager);
        
        tagManager.$tagSearch.keyup();
    }
}

var tagManager = new TagManager();
var editModManager = new EditModManager(tagManager);