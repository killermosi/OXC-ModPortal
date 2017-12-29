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
     * @returns {EditModManager}
     */
    constructor() {
        // All the processing is done here
        this.$editModForm = $('form#editMod');
        
        this.tagManager = new TagManager(this.$editModForm);
        this.backgroundManager = new BackgroundManager(this.$editModForm);
        
        // Delay before making the slug preview request, in milliseconds
        this.modSlugPreviewDelay = 500;
        
        
        this.modSlugPreviewTimer = null;
        
        // Customizations
        this.beautifyBtnGroup();
        this.$editModForm.submit(function(event){this.handleSubmit(this, event);}.bind(this));
        
        // Set the slug preview timer on every keyup event
        $('input#title', this.$editModForm).keyup(
                function(event){this.setSlugPreviewTimer(this, event);}.bind(this)
        );
        
        // Preview mod description on preview tab focus, if needed
        this.wasDescriptionChanged = false;
        this.wasDescriptionPreviewStarted = false;
        $('textarea#descriptionRaw', this.$editModForm).change(
            function(){this.wasDescriptionChanged = true;}.bind(this)
        );
        $('a[data-toggle="tab"]', this.$editModForm).on(
            'shown.bs.tab',
            function(event){this.previewModDescription(this, event);}.bind(this)
        );
    }
    
    /**
     * Set the slug preview timer
     * 
     * @param {EditModManager] self  The edit mod manager
     * @param {event}          event The event
     * @returns {undefined}
     */
    setSlugPreviewTimer(self, event) {
        clearTimeout(self.modSlugPreviewTimer);
        self.modSlugPreviewTimer = setTimeout(
            function(){self.previewModSlug(self, event);},
            self.modSlugPreviewDelay
        );
    }
    
    /**
     * Preview the mod slug based on the title
     * 
     * @param {EditModManager] self  The edit mod manager
     * @param {event]          event The event
     * @returns {undefined}
     */
    previewModSlug(self, event) {
        $.ajax(self.$editModForm.data('slug-preview-action'), {
            method: 'post',
            data: {
                title: $(event.target).val()
            },
            dataType: 'json'
        })
        .done(function(data){
            $('input#slug', self.$editModForm).val(data.slug);
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
     * @param {EditModManager] self  The edit mod manager
     * @param {event}          event The event
     * @returns {undefined}
     */
    previewModDescription(self, event) {
        var target = $(event.target);
        
        // This is not the tab we're looking for, move along
        if (target.attr('id') !== 'description-preview-tab') {
            return;
        }
        
        // Sync div height when switching to preview
        // TODO: Use another option, like a resizable tab container and CSS flex, this is hackish
        $('div#description-preview', self.$editModForm).height(
            $('div#description-edit', self.$editModForm).height()
        );
        
        // Don't make a request if nothing was changed by the user
        // or if there is a request already in progress
        if (
            self.wasDescriptionChanged === false
            || self.wasDescriptionPreviewStarted === true
        ) {
            return;
        }
        
        // Prevent other requests until this one is done
        self.wasDescriptionPreviewStarted = true;
        
        // Show the loading hint, hide the description
        $('div#description-preview-progress', self.$editModForm).removeClass('d-none');
        $('div#description-preview-content', self.$editModForm).addClass('d-none');
        
        $.ajax(self.$editModForm.data('description-preview-action'), {
            method: 'post',
            data: {
                descriptionRaw: $('textarea#descriptionRaw', self.$editModForm).val()
            },
            dataType: 'json'
        })
        .done(function(data){
            // The result goes into the preview regardless of the success status
            $('div#description-preview-content', self.$editModForm).html(data.content);
            self.wasDescriptionChanged = false;
        })
        .fail(function(){
            $('div#description-preview-content', self.$editModForm).html(
                Lang.global_unexpected_error
            );
        })
        .always(function(){
            $('div#description-preview-progress', self.$editModForm).addClass('d-none');
            $('div#description-preview-content', self.$editModForm).removeClass('d-none');
            
            self.wasDescriptionPreviewStarted = false;
        });
    }
    
    /**
     * Handle form submission
     * 
     * @param {EditModManager] self  The edit mod manager
     * @param {event}          event The event
     * @returns {undefined}
     */
    handleSubmit(self, event) {
        event.preventDefault();
        self.setLoadingState(self, true);
        
        // Collect
        
        $.ajax(self.$editModForm.attr('action'), {
            method: 'post',
            data: {
                title: $('input#title', self.$editModForm).val(),
                isPublished: $('input[name=isPublished]:checked', self.$editModForm).val(),
                summary:  $('textarea#summary', self.$editModForm).val(),
                descriptionRaw:  $('textarea#descriptionRaw', self.$editModForm).val(),
                tags: self.tagManager.selectedTags.join(','),
                background: self.backgroundManager.background
            },
            dataType: 'json'
        })
        .done(function(data){self.handleSubmitDone(self, data);})
        .fail(function(){self.handleSubmitFail(self);});
    }
    
    /**
     * Handle form success submission result
     * @param {EditModManager] self  The edit mod manager
     * @param {jqXHR}          data  The received response
     * @returns {undefined}
     */
    handleSubmitDone(self, data) {
        if (data.success) {
            window.location.href = data.content;
            return;
        }
        
        self.setLoadingState(self, false, data.content);
    }
    
    /**
     * Handle form failed submission
     * 
     * @param {EditModManager] self The edit mod manager
     * @returns {undefined}
     */
    handleSubmitFail(self) {
        self.setLoadingState(self, false, Lang.global_unexpected_error);
    }
    
    /**
     * Set the loading state for the edit mod form
     * 
     * @param {EditModManager] self    The edit mod manager
     * @param {boolean}        state   The loading state, active or not 
     * @param {string}         message The error message to show
     * @returns {undefined}
     */
    setLoadingState(self, state, message = null) {
        if (state === true) {
            $('div#error-message', self.$editModForm).addClass('d-none');
            $('button', self.$editModForm).attr('disabled','');
            $('div#progress', self.$editModForm).removeClass('d-none');
        } else {
            $('button', self.$editModForm).removeAttr('disabled');
            $('div#progress', self.$editModForm).addClass('d-none');
            $('div#error-message', self.$editModForm).removeClass('d-none').text(message);
        }
    }
}

class TagManager {
    /**
     * Setup tag editing
     * 
     * @param {object} $editModForm The edit mod form
     * @returns {TagManager}
     */
    constructor ($editModForm) {
        this.$editModForm = $editModForm;
        
        // Form elements
        this.$tagSelect = $('div#tag-select', this.$editModForm);
        this.$tagSelectNone = $('div#tag-select-none', this.$editModForm);
        this.$tagSearch = $('input#tag-search', this.$editModForm);
        this.$tagSearchResultContainer = $('div#tag-search-result', this.$editModForm);
        this.$tagSearchResultPanel = $('div', this.$tagSearchResultContainer);
        
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
        
        this.$tagSearch.keyup(function(event){this.searchTag(this, event);}.bind(this));
        
        // Render inital tags
        this.renderTagSelection(this);
    }
    
    /**
     * Search for tags matching the entered text
     * 
     * @param {TagManager} self  The tag manager
     * @param {event}      event The event
     * @returns {Boolean}
     */
    searchTag(self, event) {
        var searchResult = [];
        
        // On Enter, add the first tag in the results list (if any) to the selected tags.
        if (event.which === 13) {
            if (self.searchResult.length === 0) {
                return;
            }

            var tag = self.searchResult.shift();
            self.selectTag(tag);
            self.renderTagSearch(self);
            return;
        }
        
        // Filter searched value: https://stackoverflow.com/a/38132788/1111983
        var searchTerm = self.$tagSearch.val().replace(/([a-z0-9\-])|[^]/g, '$1');
        self.$tagSearch.val(searchTerm);
        
        // Search for matching tags
        if (searchTerm.length !== 0) {
            self.availableTags.forEach(function(tag){
                if (
                    (tag.includes(searchTerm) === true || searchTerm === '*')
                    && self.selectedTags.indexOf(tag) === -1
                ) {
                    searchResult.push(tag);
                }
            });
        }
        
        if (self.searchResult.toString() === searchResult.toString()) {
            return;
        }

        self.searchResult = searchResult;
        self.renderTagSearch(self);
    }
    
    /**
     * Render the searched tags on the form
     * 
     * @param {TagManager} self The tag manager
     * @returns {undefined}
     */
    renderTagSearch (self) {
        if (self.searchResult.length === 0) {
            self.$tagSearchResultContainer.addClass('d-none');
            return;
        }
        
        self.$tagSearchResultPanel.empty();
        self.$tagSearchResultContainer.removeClass('d-none');

        self.searchResult.forEach(function(tag){
            var $tag = $('<a />', {href: '#', class: 'badge badge-primary p-2 mr-2 mb-2'});
            $tag.text(tag);
            $tag.click(function(event){
                event.preventDefault();
                self.selectTag(self, tag);
            });
            self.$tagSearchResultPanel.append($tag);
        });
    }
    
    /**
     * Render the selected tags on the form
     * 
     * @param {TagManager} self The tag manager
     * @returns {undefined}
     */
    renderTagSelection (self) {
        if (self.selectedTags.length === 0) {
            self.$tagSelect.addClass('d-none');
            self.$tagSelectNone.removeClass('d-none');
            return;
        }
        
        self.$tagSelect.removeClass('d-none').empty();
        self.$tagSelectNone.addClass('d-none');
        
        self.selectedTags.forEach(function(tag){
            var $tag = $('<a />', {href: '#', class: 'badge badge-primary p-2 mr-2 mb-2'});
            $tag.text(tag);
            $tag.click(function(event){
                event.preventDefault();
                self.removeTag(self, tag);
            });
            self.$tagSelect.append($tag);
        });
    }
    
    /**
     * Add a tag to the selected tags list
     * 
     * @param {TagManager} self The tag manager
     * @param {string}     tag  The tag
     * @returns {undefined}
     */
    selectTag(self, tag) {
        self.selectedTags.push(tag);
        self.selectedTags.sort();
        self.$tagSearch.val('').keyup();
        self.renderTagSelection(self);
    }
    
    /**
     * Remove a tag from the selected tags list
     * 
     * @param {TagManager} self The tag manager
     * @param {string}     tag  The tag
     * @returns {undefined}
     */
    removeTag (self, tag) {
        var index = self.selectedTags.indexOf(tag);
        self.selectedTags.splice(index, 1);
        
        // Update the search panel, if open
        self.renderTagSelection(self);
        
        self.$tagSearch.keyup();
    }
}

class BackgroundManager {
    /**
     * Setup background editing
     * 
     * @param {object} $editModForm The edit mod form
     * @returns {BackgroundManager}
     */
    constructor($editModForm) {
        // Form elements
        this.$editModForm = $editModForm;
        this.$backgroundImage = $('img#background-image', this.$editModForm);
        this.$backgroundImageUpload = $('input#background-image-upload', this.$editModForm);
        this.$btnUploadMod = $('button#upload-background', this.$editModForm);
        this.$btnDefaultMod = $('button#default-background', this.$editModForm);
        this.$progressBarContainer = $('div#background-progress', this.$editModForm);
        this.$progressBar = $(':first-child', this.$progressBarContainer);
        this.$message = $('div#background-message', this.$editModForm);
        
        this.$btnUseDefaultBackground = $('button#use-default-background');
        this.$body = $('body#body');
        
        this.setFormState(this, false, false);
        
        // Enable delete button if a background image is available
        if (this.$backgroundImage.attr('src') !== this.$backgroundImage.data('default-background-url')) {
            this.$btnDefaultMod.removeAttr('disabled');
        }
        
        // Handle events
        this.$btnUploadMod.click(function(){this.$backgroundImageUpload.click();}.bind(this));
        this.$backgroundImageUpload.change(function(){this.handleUpload(this);}.bind(this));
        this.$btnUseDefaultBackground.click(function(){this.handleDefaultBackground(this);}.bind(this));
        
        // File data
        this.file = null;
        this.currentChunk = 0;
        this.totalChunks = null;
        
        this.chunkSize = parseInt(this.$editModForm.data('chunk-size'), 10);
        
        // Background image status:
        // - 0: no changes this session
        // - 1: use default background
        // - uuid: the last uploaded background image temporary UUID
        this.background = 0;
    }
    
    /**
     * Delete the current mod background
     * 
     * @returns {undefined}
     */
    handleDelete() {
        console.log('delete');
    }
    
    /**
     * Upload the selected background file
     * 
     * @param {BackgroundManager} self The background manager
     * @returns {undefined}
     */
    handleUpload(self) {
        var files = self.$backgroundImageUpload.prop('files');
        
        // Nothing to do if no files were selected
        if (files.length === 0) {
            return;
        }
        
        self.setFormState(self, true, false);
        
        try {
            new FileUpload(
                files[0],
                'background',
                self.$editModForm.data('create-upload-slot-action'),
                self.$editModForm.data('upload-file-chunk-action'),
                self.$editModForm.data('chunk-size'),
                function(response){self.handleUploadSuccess(self, response);},
                function(progress){self.handleUploadProgress(self, progress);},
                function(){self.handleUploadfail(self, Lang.global_unexpected_error);}
            );
        } catch (e) {
            if (e === 404) {
                self.setFormState(self, false, true, Lang.global_chunk_upload_unsupported);
            } else {
                self.setFormState(self, false, true, Lang.global_unexpected_error);
            }
        }
    }
    
    /**
     * Handle upload success
     * 
     * @param {BackgroundManager} self    The background manager
     * @param {object}           response The response
     * @returns {undefined}
     */
    handleUploadSuccess(self, response)
    {
        if (response.hasOwnProperty('success') === false || response.hasOwnProperty('message') === false) {
            self.setFormState(self, false, false, Lang.global_unexpected_error);
            return;
        }
        
        if (response.success === false) {
            self.setFormState(self, false, false, response.message);
            return;
        }

        // Success, set the background to both the preview and to the actual page for effect
        // Load the image first, and then set it in the page (saves one duplicate processing on the backend)
        var img = new Image();
        img.onload = function(){
            self.$backgroundImage.attr('src', response.message);
            self.$body.css('background-image', 'url("' + response.message + '")');
            self.setFormState(self, false, true, Lang.page_editmod_success_background);
            
            // Store the new background UUID only after the image was loaded and the user had the opportunity to
            // review it
            
            self.background = response.slotUuid;
        };
        img.src = response.message;
    }
    
    /**
     * Handle upload fail
     * 
     * @param {BackgroundManager} self    The background manager
     * @param {string}            message Failure message
     * @returns {undefined}
     */
    handleUploadFail(self, message) {
        self.setFormState(self, false, false, message);
    }
    
    /**
     * Handle upload progress
     * 
     * @param {BackgroundManager} self     The background manager
     * @param {number}            progress Upload progress
     * @returns {undefined}
     */
    handleUploadProgress(self, progress) {
        self.$progressBar.css('width', progress + '%');
    }
    
    /**
     * Revert to the default background
     * 
     * @param {BackgroundManager} self The background manager
     * @returns {undefined}
     */
    handleDefaultBackground(self) {
        self.setFormState(self, true);
        
        var defaultBackgroundImageUrl = self.$backgroundImage.data('default-background-url');
        
        var img = new Image();
        
        img.onload = function(){
            // Show the default background
            self.$backgroundImage.attr('src', defaultBackgroundImageUrl);
            self.$body.css('background-image', 'url("' + defaultBackgroundImageUrl + '")');

            // Store the action
            self.background = 1;

            // Update the message
            self.setFormState(self, false, true, Lang.page_mymods_success_background_default);
        };
        
        img.src = defaultBackgroundImageUrl;
    }
    
    /**
     * Set the form state of the upload mod section
     * 
     * @param {BackgroundManager} self    The background manager
     * @param {boolean}           state   The loading state, active or not
     * @param {boolean}           success If the result is a success or an error
     * @param {string}            message The message to show
     * @returns {undefined}
     */
    setFormState(self, state, success = false, message = null) {
        if (state === true) {
            self.$btnUploadMod.attr('disabled', '');
            self.$btnDefaultMod.attr('disabled', '');
            self.$progressBarContainer.removeClass('d-none');
            self.$progressBar.css('width', '0%');
            self.$message.addClass('d-none');
        } else {
            self.$btnUploadMod.removeAttr('disabled');
            
            if (self.$backgroundImage.attr('src') !== self.$backgroundImage.data('default-background-url')) {
                self.$btnDefaultMod.removeAttr('disabled');
            } else {
                self.$btnDefaultMod.attr('disabled', '');
            }
            
            self.$progressBarContainer.addClass('d-none');
            
            if (message !== null) {
                self.$message.removeClass('d-none')
                             .text(message)
                             .addClass(success ? 'alert-success' : 'alert-warning')
                             .removeClass(success ? 'alert-warning' : 'alert-success');
            }
        }
    }
}

/**
 * Upload a file in chunks
 */
class FileUpload {
    /**
     * Class initialization
     * 
     * @param {File}   file                  File data to upload
     * @param {string} type                  The file type
     * @param {string} slotUrl               The URL to call in order to create an upload slot
     * @param {string} chunkUrl              The URL to call in order to upload a file chunk
     * @param {string} chunkSize             Chunk size, in bytes
     * @param {object} doneCallback          Callback to be executed when the upload finishes for either success or
     *                                       failure - the server response is passed as a parameter, along with the slot
     *                                       UUID on success
     * @param {object} progressCallback      Callback to be executed when the upload data gets sent
     * @param {object} retryExceededCallback Callback to be executed before a chunk is uploaded
     * @returns {FileUpload}
     * @throws {404} Missing technical support from browser
     */
    constructor(file, type, slotUrl, chunkUrl, chunkSize, doneCallback, progressCallback, retryExceededCallback) {
        // Check for browser support
        if (typeof FormData === 'undefined') {
            console.log('FormData is not supported by the browser');
            throw 404;
        }
        
        this.file                  = file;
        this.type                  = type;
        this.slotUrl               = slotUrl;
        this.chunkUrl              = chunkUrl;
        this.chunkSize             = chunkSize;
        this.doneCallback          = doneCallback;
        this.progressCallback      = progressCallback;
        this.retryExceededCallback = retryExceededCallback;
        
        // Chunk details
        this.curentChunk = 0;
        this.totalChunks = Math.ceil(this.file.size / this.chunkSize);
        
        // Upload retry
        this.retryCount = 0;
        this.maxRetry = 3;
        
        // The upload slot UUID
        this.slotUuid = null;
        
        // Check that the slice functionality is supported
        if (!this.file.slice && !this.file.webkitSlice && !this.file.mozSlice) {
            throw 404;
        }
        
        // Set file slice function
        this.sliceFunction = 
                this.file.slice       ? this.file.slice :       // Standard API
                this.file.webkitSlice ? this.file.webkitSlice : // Chrome family
                this.file.mozSlice    ? this.file.mozSlice :    // Firefox family
                // Feature not supported
                function(){
                    throw 404;
                };
        
        this.uploadFile(this);
    }
    
    /**
     * Upload the file
     * 
     * @param {FileUpload} self The FileUpload instance
     * @returns {undefined}
     */
    uploadFile(self) {
        // Create an upload slot
        $.ajax(self.slotUrl, {
            method: 'post',
            data: {
                type: self.type,
                size: self.file.size,
                name: self.file.name
            },
            dataType: 'json'
        })
        .done(function(response){
            // On failure pass response
            if (response.success === false) {
                self.doneCallback(response);
                return;
            }
            
            // On success store the slot UUID and start uploading chunks
            self.slotUuid = response.message;
            self.processNextChunk(self);
        })
        .fail(self.doneCallback)
        .progress(function(event){
            console.log('JQProgress',event.loaded, event.total);
        });
    }
    
    /**
     * Prepare the next chunk for upload
     * 
     * @param {FileUpload} self The FileUpload instance
     * @returns {undefined}
     */
    processNextChunk(self) {
        // Prepare chunk
        self.curentChunk++;
        self.retryCount = 0;
        self.uploadChunk(self);
    }
    
    /**
     * Upload the current chunk
     * 
     * @param {FileUpload} self The FileUpload instance
     * @returns {undefined}
     */
    uploadChunk(self) {
        // Stop on too many upload attempts
        if (self.retryCount > self.maxRetry) {
            self.retryExceededCallback();
            return;
        }
        
        self.retryCount++;
        
        var data = new FormData();
        data.append('slotUuid', self.slotUuid);
        data.append('chunkData', self.getFileChunk(self));
        
        $.ajax(self.chunkUrl, {
            method: 'post',
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            dataType: 'json',
            xhr: function () {
                var xhr = new window.XMLHttpRequest();

                //Upload progress
                xhr.upload.onprogress = function(evt){
                    var progressPercentage = Math.floor(
                        ((self.curentChunk - 1) * self.chunkSize + evt.loaded) * 100 / self.file.size
                    );
        
                    self.progressCallback(progressPercentage);
                };
                
                return xhr;
            }
        })
        .done(function(response){
            // On failure pass response
            if (response.success === false) {
                self.doneCallback(response);
                return;
            }
            
            // On success, upload the next chunk if there is any
            if (self.curentChunk !== self.totalChunks) {
                self.processNextChunk(self);
                return;
            }
            
            // Otherwise, this means that all chunks have been uploaded
            response.slotUuid = self.slotUuid;
            self.doneCallback(response);
        })
        .fail(self.doneCallback);
    }
    
    /**
     * Get the next chunk from the file
     * 
     * @param {FileUpload} self The FileUpload instance
     * @returns {object}
     */
    getFileChunk(self) {
        var start = (self.curentChunk - 1) * self.chunkSize;
        var end = start + self.chunkSize;
        
        if (end > self.file.size) {
            end = self.file.size;
        }

        return self.sliceFunction.bind(self.file)(start, end);
    }
}

// Boot it up
$(function(){
    new EditModManager();
});
