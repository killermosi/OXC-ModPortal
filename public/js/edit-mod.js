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

// TODO: Replace all attr() and removeAttr() calls with prop(true/false) calls

/**
 * Edit mod manager
 * 
 * @type EditModManager
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
        this.fileManager = new FileManager(this.$editModForm);
        
        this.deleteManager = new DeleteManager(this.$editModForm);
        
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
        
        $.ajax(self.$editModForm.attr('action'), {
            method: 'post',
            data: {
                title: $('input#title', self.$editModForm).val(),
                isPublished: $('input[name=isPublished]:checked', self.$editModForm).val(),
                summary:  $('textarea#summary', self.$editModForm).val(),
                descriptionRaw:  $('textarea#descriptionRaw', self.$editModForm).val(),
                tags: self.tagManager.getSelectedTags(),
                backgroundUuid: self.backgroundManager.getBackgroundUuid(),
                images: self.fileManager.getImages(),
                resources: self.fileManager.getResources()
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
        $('button#btn-delete-mod', self.$editModForm).removeClass('mr-auto');
        
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

/**
 * Tag manager
 * 
 * @type TagManager
 */
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
     * Get the selected tags
     * 
     * @returns {string}
     */
    getSelectedTags()
    {
        return this.selectedTags.join(',');
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

/**
 * Background manager
 * 
 * @type BackgroundManager
 */
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
        this.$btnUploadBackground = $('button#upload-background', this.$editModForm);
        this.$btnDefaultBackground = $('button#default-background', this.$editModForm);
        this.$progressBarContainer = $('div#background-progress', this.$editModForm);
        this.$progressBar = $(':first-child', this.$progressBarContainer);
        this.$message = $('div#background-message', this.$editModForm);
        
        this.$btnUseDefaultBackground = $('button#use-default-background');
        this.$body = $('body#body');
        
        // Configure the upload form initial state
        this.setFormState(this, false, false);
        
        // Handle events
        this.$btnUploadBackground.click(function(){this.$backgroundImageUpload.click();}.bind(this));
        this.$backgroundImageUpload.change(function(){this.handleUpload(this);}.bind(this));
        this.$btnUseDefaultBackground.click(function(){this.handleDefaultBackground(this);}.bind(this));
        
        // File data
        this.file = null;
        this.currentChunk = 0;
        this.totalChunks = null;
        
        this.chunkSize = parseInt(this.$editModForm.data('chunk-size'), 10);
    }
    
    /**
     * Get the background UUID
     * 
     * @returns {string}
     */
    getBackgroundUuid()
    {
        return this.$backgroundImage.data('background-uuid');
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
            self.$backgroundImage.attr('src', response.message.url);
            self.$body.css('background-image', 'url("' + response.message.url + '")');
            self.$backgroundImage.data('background-uuid', response.slotUuid);
            self.setFormState(self, false, true, Lang.page_editmod_success_background);
        };
        img.src = response.message.url;
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
        self.$progressBar.attr('aria-valuenow', progress);
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

            // Set background UUID to empty, this means default background
            self.$backgroundImage.data('background-uuid', '');

            // Update the message
            self.setFormState(self, false, true, Lang.page_editmod_success_background_default);
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
            self.$btnUploadBackground.attr('disabled', '');
            self.$btnDefaultBackground.attr('disabled', '');
            self.$progressBarContainer.removeClass('d-none');
            self.$progressBar.css('width', '0%');
            self.$message.addClass('d-none');
        } else {
            self.$btnUploadBackground.removeAttr('disabled');
            console.log('Background UUID:',self.$backgroundImage.data('background-uuid'));
            if (self.$backgroundImage.data('background-uuid').length !== 0) {
                self.$btnDefaultBackground.removeAttr('disabled');
            } else {
                self.$btnDefaultBackground.attr('disabled', '');
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
 * Manage mod files
 */
class FileManager {
    /**
     * Class initialization
     * 
     * @param {object} $editModForm The form
     * @returns {FileManager}
     */
    constructor($editModForm) {
        // File types
        this.typeResource = 0;
        this.typeImage = 1;
        
        this.$editModForm = $editModForm;
        
        this.$uploadImageInput = $('input#upload-image', this.$editModForm);
        this.$uploadImageBtn = $('button#add-image', this.$editModForm);
        
        this.$uploadResourceInput = $('input#upload-resource', this.$editModForm);
        this.$uploadResourceBtn = $('button#add-resource', this.$editModForm);
        
        this.$imageList = $('div#image-list', this.$editModForm);
        this.$resourceList = $('div#resource-list', this.$editModForm);
        
        this.$imageCardSample = $('div#image-card-sample', this.$editModForm).children().first();
        this.$imageCardUpload = this.$imageList.children().last();
        
        this.$resourceCardSample = $('div#resource-card-sample', this.$editModForm).children().first();
        this.$resourceCardUpload = this.$resourceList.children().last();
        
        this.$editFileModal = $('div#edit-file');
        this.$editFileImage = $('img', this.$editFileModal);
        
        this.$editFileTitleResource = $('h5#edit-file-title-resource', this.$editFileModal);
        this.$editFileTitleImage = $('h5#edit-file-title-image', this.$editFileModal);
        
        this.$editFileExtResource = $('span#edit-file-ext-resource', this.$editFileModal);
        this.$editFileExtImage = $('span#edit-file-ext-image', this.$editFileModal);
        
        this.$editFileDescritption = $('input#file-caption', this.$editFileModal);
        this.$editFileFilename = $('input#file-filename', this.$editFileModal);
        this.$editFileOrder = $('input#file-order', this.$editFileModal);
        
        this.$editFileBtnDeleteCancel = $('button#file-delete-cancel', this.$editFileModal);
        this.$editFileBtnDeleteConfirm = $('button#file-delete-confirm', this.$editFileModal);
        this.$editFileBtnDelete = $('button#file-delete', this.$editFileModal);
        this.$editFileBtnUpdate = $('button#file-update', this.$editFileModal);
        this.$editFileBtnClose = $('button#file-close', this.$editFileModal);
        
        this.$editImageProgress = $('div.progress', this.$editFileModal);
        this.$editImageErrorMessage = $('div#file-error-message', this.$editFileModal);
        
        this.canCloseEditFileModal = true;
        this.imageValidationUrl = this.$editModForm.data('validate-mod-file-action');
        
        this.$editFileBtnDelete.click(function(){
            this.setModalState(this, true);
        }.bind(this));
        
        this.$editFileBtnDeleteConfirm.click(function(){
            this.$editFileModal.data('card').remove();
            this.$editFileModal.modal('hide');
        }.bind(this));
        
        this.$editFileBtnDeleteCancel.click(function(){
            this.setModalState(this, false);
        }.bind(this));
        
        this.$editFileBtnUpdate.click(function(){
            this.handleUpdate(this);
        }.bind(this));
        
        // Handle events
        this.$uploadImageBtn.click(function(){
            this.$uploadImageInput.click();
        }.bind(this));
        
        this.$uploadImageInput.change(function(){
            this.handleUpload(this, this.typeImage);
        }.bind(this));
        
        this.$uploadResourceBtn.click(function(){
            this.$uploadResourceInput.click();
        }.bind(this));
        
        this.$uploadResourceInput.change(function(){
            this.handleUpload(this, this.typeResource);
        }.bind(this));
        
        this.$editFileModal.on('show.bs.modal', function(event){
            this.handleEditModalOpen(this, $(event.relatedTarget).parent().parent().parent());
        }.bind(this));
        
        this.$editFileModal.on('hide.bs.modal', function(event){
            if (this.canCloseEditImageModal === false) {
                event.preventDefault();
            }
        }.bind(this));
    }
    
    /**
     * Retrieve a list of images and their properties as a JSON-encoded string
     * 
     * @returns {string}
     */
    getImages() {
        var imagesList = new Array();

        this.$imageList.children().each(function(){
            var $imageCard = $(this);
            
            if (!$imageCard.data('uuid')) {
                return;
            }
            
            var image = {
                uuid: $imageCard.data('uuid'),
                filename: String($imageCard.data('filename')),
                description: String($imageCard.data('description'))
            };
            
            imagesList.push(image);
        });
        
        return JSON.stringify(imagesList);
    }
    
    /**
     * Retrieve a list of resources and their properties as a JSON-encoded string
     * 
     * @returns {string}
     */
    getResources() {
        var resourcesList = new Array();

        this.$resourceList.children().each(function(){
            var $resourceCard = $(this);
            
            if (!$resourceCard.data('uuid')) {
                return;
            }
            
            var image = {
                uuid: $resourceCard.data('uuid'),
                filename: String($resourceCard.data('filename')),
                description: String($resourceCard.data('description'))
            };
            
            resourcesList.push(image);
        });
        
        return JSON.stringify(resourcesList);
    }
    
    /**
     * Handle file(s) upload
     * 
     * @param {FileManager} self The ImageManager
     * @param {number}      type The upload type
     * @returns {undefined}
     */
    handleUpload(self, type){
        
        if (type === self.typeImage) {
            var files = self.$uploadImageInput.prop('files');
            var filesType = 'image';
        } else {
            var files = self.$uploadResourceInput.prop('files');
            var filesType = 'resource';
        }
        
        // Nothing to do if no files were selected
        if (files.length === 0) {
            return;
        }
        
        new MultiUpload(
            files,
            filesType,
            self.$editModForm,
            function(response){
                self.handleUploadCallback(self, type, response);
            }
        );
    }
    
    /**
     * Handle callback for a successfully uploaded file
     * 
     * @param {FileManager} self     The ImageManager
     * @param {number}      type The upload type
     * @param {object}      response The server response
     * @returns {undefined}
     */
    handleUploadCallback(self, type, response) {
        
        if (response.success === false) {
            return;
        }
        
        var $imageCard = (type === self.typeImage)
            ? self.$imageCardSample.clone()
            : self.$resourceCardSample.clone();
        
        $imageCard.data('uuid', response.slotUuid);
        $imageCard.data('filename', response.message.name);
        
        // Add it before the last card (the "add" card)
        if (type === self.typeImage) {
            $('img', $imageCard).attr('src', response.message.url);
            self.$imageCardUpload.before($imageCard);
        } else {
            $('input', $imageCard).val(response.message.name);
            self.$resourceCardUpload.before($imageCard);
        }
    }
    
    /**
     * Set the modal state
     * 
     * @param {FileManager} self      The ImageManager
     * @param {boolean}      forDelete If the state is for delete confirmation
     * @returns {undefined}
     */
    setModalState(self, forDelete = false) {
        // The progress and the error message must be always hidden on open
        self.$editImageProgress.addClass('d-none');
        self.$editImageErrorMessage.addClass('d-none');
        
        if (forDelete) {
            self.$editFileBtnDeleteCancel.removeClass('d-none');
            self.$editFileBtnDeleteConfirm.removeClass('d-none');
            self.$editFileBtnDelete.addClass('d-none');
            self.$editFileBtnUpdate.addClass('d-none');
            self.$editFileBtnClose.addClass('d-none');
        } else {
            self.$editFileBtnDeleteCancel.addClass('d-none');
            self.$editFileBtnDeleteConfirm.addClass('d-none');
            self.$editFileBtnDelete.removeClass('d-none').addClass('mr-auto');
            self.$editFileBtnUpdate.removeClass('d-none');
            self.$editFileBtnClose.removeClass('d-none');
        }
    }
    
    /**
     * Set the modal loading state
     * 
     * @param {FileManager} self         The ImageManager
     * @param {boolean}      loading      The loading state
     * @param {string}       errorMessage The error message
     * @returns {undefined}
     */
    setModalLoadingState(self, loading = false, errorMessage = false)
    {
        if (loading === true) {
            self.$editFileBtnDelete.attr('disabled','');
            self.$editFileBtnUpdate.attr('disabled','');
            self.$editFileBtnClose.attr('disabled','');
            
            self.canCloseEditFileModal = false;
            
            self.$editImageErrorMessage.addClass('d-none');
            self.$editImageProgress.removeClass('d-none');
        } else {
            self.$editFileBtnDelete.removeAttr('disabled');
            self.$editFileBtnUpdate.removeAttr('disabled');
            self.$editFileBtnClose.removeAttr('disabled');
            
            self.canCloseEditFileModal = true;
            self.$editImageProgress.addClass('d-none');
            
            if (errorMessage) {
                self.$editFileBtnDelete.removeClass('mr-auto');
                self.$editImageErrorMessage.removeClass('d-none').text(errorMessage);
            }
        }
    }
    
    /**
     * Populate the edit image modal with the correct data of a image card
     * 
     * @param {FileManager} self       The ImageManager
     * @param {object}       $imageCard The image card
     * @returns {undefined}
     */
    handleEditModalOpen(self, $imageCard) {
        
        var type = $imageCard.data('type');
        
        // Configure the modal according to the file type
        if (type === self.typeResource) {
            self.$editFileImage.addClass('d-none');
            
            self.$editFileTitleResource.removeClass('d-none');
            self.$editFileTitleImage.addClass('d-none');
            
            // Rounded corners are applied only to the last child
            self.$editFileExtResource.removeClass('d-none').appendTo(self.$editFileExtResource.parent());
            self.$editFileExtImage.addClass('d-none');
        } else if (type === self.typeImage) {
            var imgSrc = $('img', $imageCard).attr('src');
            self.$editFileImage.attr('src', imgSrc).attr('alt', description);
            self.$editFileImage.removeClass('d-none');

            self.$editFileTitleResource.addClass('d-none');
            self.$editFileTitleImage.removeClass('d-none');
            
            self.$editFileExtResource.addClass('d-none');
            // Rounded corners are applied only to the last child
            self.$editFileExtImage.removeClass('d-none').appendTo(self.$editFileExtImage.parent());;
        }
        
        // Set common data
        var description = $imageCard.data('description');
        var filename = $imageCard.data('filename');
        var order = $imageCard.index() + 1; // Order starts from one (visually)
        
        self.$editFileDescritption.val(description);
        self.$editFileFilename.val(filename);
        self.$editFileOrder.val(order);
        
        self.$editFileModal.data('card', $imageCard);
        
        self.setModalState(self, false);
    }
    
    /**
     * Validate the data and update the current image card
     * 
     * @param {FileManager} self The ImageManager
     * @returns {undefined}
     */
    handleUpdate(self) {
        // Validate the image data
        self.setModalLoadingState(self, true);
        
        //TODO: Don't make the request if the details were not changed        
        $.ajax(self.imageValidationUrl, {
            method: 'post',
            data: {
                uuid: self.$editFileModal.data('card').data('uuid'),
                description: String(self.$editFileDescritption.val()),
                filename: String(self.$editFileFilename.val()),
                order: String(self.$editFileOrder.val())
            },
            dataType: 'json'
        })
        .done(function(response){
            if (response.success) {
                self.setModalLoadingState(self, false);
                self.processUpdate(self);
            } else {
                self.setModalLoadingState(self, false, response.content);
            }
        })
        .fail(function(){self.setModalLoadingState(self, false, Lang.global_unexpected_error);});
    }
    
    /**
     * Process the updated data
     * 
     * @param {FileManager} self The ImageManager
     * @returns {undefined}
     */
    processUpdate(self) {
        self.$editFileModal.modal('hide');
        
        var $imageCard = self.$editFileModal.data('card');
        var currentIndex = $imageCard.index();
        
        var rescription = self.$editFileDescritption.val();
        var filename = self.$editFileFilename.val();

        var type = $imageCard.data('type');

        if (type === self.typeResource) {
            $('input', $imageCard).val(filename);
        } else if (type === self.typeImage) {
            $('input', $imageCard).val(rescription);
        }
        
        $imageCard.data('description', rescription);
        $imageCard.data('filename', filename);
        
        var imageCards = $imageCard.parent().children();
        var itemsCount = imageCards.length - 1; // Last one is the "add image" card
        
        // Update order
        var order = parseInt(self.$editFileOrder.val());
        
        // Make adjustments to the order if needed
        // TODO: Make a more elegant version, this one just works
        order = order || 1;
        
        if (order <= 0) {
            order = 1;
        }
        
        if (order > itemsCount) {
            order = itemsCount;
        }
        
        // Stop if the position has not changed
        if (order - 1 === $imageCard.index()) {
            return;
        }
        
        if (order > currentIndex) {
            order++; // The element is removed, freeing up a space in the list
        }

        imageCards[order - 1].before($imageCard[0]);
    }
}

/**
 * Handle mod deletion
 */
class DeleteManager {
    /**
     * Class initialization
     * 
     * @param {object} $editModForm The delete form
     * @returns {DeleteManager}
     */
    constructor($editModForm) {
        
        this.codeLength = $editModForm.data('delete-mod-code-length');
        this.modDeleteUrl = $editModForm.data('delete-mod-action');
        
        this.canClose = true;
        
        this.$modal = $('div#delete-mod');
        
        this.$deleteCodeInput = $('input#delete-code', this.$modal);
        this.$deleteConfirmBtn = $('button#delete-confirm', this.$modal);
        this.$deleteCloseBtn = $('button#delete-close', this.$modal);
        
        this.$progressBar = $('div.progress', this.$modal);
        this.$errorMessage = $('div#delete-error-message', this.$modal);
        
        // Configure the modal to be empty on each activation
        this.$modal.on('show.bs.modal', function(){
            this.$deleteCodeInput.val('');
            this.$deleteConfirmBtn.prop('disabled', true);
            this.$progressBar.addClass('d-none');
            this.$errorMessage.addClass('d-none');
        }.bind(this));
        
        // Activate the delete button only if the typed code has the proper length
        this.$deleteCodeInput.keyup(function(event){
            // TODO: remove everything except digits from the input
            var disabled = (this.$deleteCodeInput.val().length !== this.codeLength);

            this.$deleteConfirmBtn.prop('disabled', disabled);
            
        }.bind(this));
        
        // Delete handler
        this.$deleteConfirmBtn.click(function(event){
            this.handleSubmit(this);
        }.bind(this));
        
        // Don't close if there is something going on
        this.$modal.on('hide.bs.modal', function(event){
            if (this.canClose === false) {
                event.preventDefault();
            }
        }.bind(this));
        
    }
    
    /**
     * Set the modal loading state
     * 
     * @param {DeleteManager} self    The DeleteManager
     * @param {boolean}       state   The loading state
     * @param {string}        message Message to display
     * @returns {undefined}
     */
    setLoadingState(self, state, message = false) {
        if (state === true) {
            self.canClose = false;
            
            self.$deleteCodeInput.prop('disabled', true);
            self.$deleteConfirmBtn.prop('disabled', true);
            self.$deleteCloseBtn.prop('disabled', true);
            
            self.$progressBar.removeClass('d-none');
            self.$errorMessage.addClass('d-none');
        } else {
            self.canClose = true;
            
            self.$deleteCodeInput.prop('disabled', false);
            self.$deleteConfirmBtn.prop('disabled', false);
            self.$deleteCloseBtn.prop('disabled', false);
            
            self.$progressBar.addClass('d-none');
            self.$errorMessage.removeClass('d-none').text(message);
        }
    }
    
    /**
     * Handle the submit event
     * @param {type} self
     * @returns {undefined}
     */
    handleSubmit(self) {
        var data = {
            deleteCode: this.$deleteCodeInput.val()
        };
        
        self.setLoadingState(self, true);
        
        $.ajax(self.modDeleteUrl, {
            method: 'post',
            data: data,
            dataType: 'json'
        })
        .done(function(response){
            // On failure show error
            if (response.success === false) {
                self.setLoadingState(self, false, response.content);
                return;
            }
            
            // On success redirect to the indicated page
            window.location.href = response.content;
        })
        .fail(function(){
            self.setLoadingState(self, false, Lang.global_unexpected_error);
        });
    }
}

/**
 * Upload multiple files while displaying the status in a nice interface
 */
class MultiUpload {
    /**
     * Class initialization
     * 
     * @param {array}    files          The files to upload
     * @param {string}   filesType      The type of the uploade files
     * @param {object}   $editModForm   The mod edit form
     * @param {function} uploadCallback Callback for each successfully uploaded file, the server response is passed as
     *                                  parameter
     * @returns {MultiUpload}
     */
    constructor(files, filesType, $editModForm, uploadCallback) {
        this.files = files;
        this.type = filesType;
        this.$editModForm = $editModForm;
        this.uploadCallback = uploadCallback;
        
        this.$modal = $('div#multi-upload');

        // If the modal can be manually closed
        this.canClose = false;
        
        this.$modal.on('hide.bs.modal', function(event){
            if (this.canClose === false) {
                event.preventDefault();
            }
        }.bind(this));
        
        // Get handles to various modal elements
        this.$content = $('div.modal-content', this.$modal);
        
        this.$body = $('div.modal-body', this.$modal);
        this.$footer = $('div.modal-footer', this.$modal);
        this.$progressContainer = $('div.progress', this.$footer);
        this.$progressBar = $('div.progress-bar', this.$progressContainer);
        this.$alert = $('div.alert', this.$footer);
        this.$btnClose = $('button.btn-secondary', this.$footer);
        this.$btnAbort = $('button.btn-danger', this.$footer);
        
        this.$fileStatusSample = $('div#file-card-sample', this.$modal).children();
        
        // Reset the modal
        $('h5#multi-upload-title', this.$modal).find('span').text(files.length);
        this.$body.empty();
        
        if (this.files.length > 1) {
            this.$progressContainer.removeClass('d-none');
        } else {
            this.$progressContainer.addClass('d-none');
        }
        
        this.$progressBar.css('width', '0%');
        this.$alert.removeClass('alert-warning').removeClass('alert-success').addClass('d-none');
        this.$btnClose.addClass('d-none');
        this.$btnAbort.removeClass('d-none').off().click(function(){this.handleAbort(this);}.bind(this));

        // List files to upload, store the displayed elements
        this.totalSize = 0;
        this.fileStatus = [];
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            // Total file size
            this.totalSize += file.size;
            
            var $fileStatus = this.$fileStatusSample.clone();
            $fileStatus.addClass('mb-2');
            $('li.list-group-item', $fileStatus).first().text(file.name);
            
            this.$body.append($fileStatus);
            this.fileStatus.push($fileStatus);
        }
        // Remove the bottom margin from the very last file element
        $fileStatus.removeClass('mb-2');

        // Start uploading only once the modal is completly displayed
        this.$modal.on('shown.bs.modal', function(){
            this.handleNextFile(this);
        }.bind(this));
        
        // Remove the event listener when the modal is hidden
        this.$modal.on('hidden.bs.modal', function(){
            this.$modal.off();
        }.bind(this));

        // Curent file (starts at negative 1 for reasons)
        this.fileIndex = -1;

        // Status
        this.uploadSuccess = 0;
        this.uploadFail = 0;
        
        // The file uploader
        this.fileUpload = null;
        
        // Show the dialog
        this.$modal.modal('show');
    }
    
    /**
     * Set the modal finished state
     * 
     * @param {MultiUpload} self    The MultiUpload instance
     * @param {boolean}     state   Success or not
     * @param {string}      message The message to display
     * @returns {undefined}
     */
    setFinishedState(self, state, message) {
        // The modal can be closed now (but force it open for half a second,
        // so that the user gets a chance to see the message)
        setTimeout(
            function(){self.canClose = true;}
            , 500
        );

        // Show status
        self.$btnClose.removeClass('d-none');
        self.$btnAbort.addClass('d-none');
        self.$progressContainer.addClass('d-none');
        
        var alertClass = state ? 'alert-success' : 'alert-warning';
        self.$alert.removeClass('d-none').addClass(alertClass).html(message);
    }
    
    /**
     * Set the completion status of the current active file
     * 
     * @param {MultiUpload} self    The MultiUpload instance
     * @param {boolean}     status  Success or failure
     * @param {string}      message The message
     * @returns {undefined}
     */
    setFileStatus(self, status, message = null) {
        var $fileStatus = this.fileStatus[self.fileIndex];
        
        $fileStatus.find('div.progress-bar')
            .removeClass('progress-bar-animated')
            .removeClass('progress-bar-striped')
            .addClass(status ? 'bg-success' : 'bg-danger')
            .css('width', '100%')
            .text(message);
    
        // Increment appropiate counter
        if (status) {
            self.uploadSuccess++;
        } else {
            self.uploadFail++;
        }
    }
    
    /**
     * Handle upload abort
     * // TODO: This does not work properly
     * 
     * @returns {undefined}
     */
    handleAbort() {
        if (this.fileUpload !== null) {
            this.fileUpload.abort();
        }
        
        var message = Lang.page_editmod_error_multi_upload_abort.replace('X', this.uploadSuccess);
        this.setFileStatus(this, false, Lang.page_editmod_error_file_upload_abort);
        this.setFinishedState(this, false, message);
    }
    /**
     * Upload the next file in the list
     * 
     * @param {MultiUpload} self The MultiUpload instance
     * @returns {undefined}
     */
    handleNextFile(self) {
        // Increment the index
        self.fileIndex++;

        // Check that the upload is finished
        if (self.fileIndex === self.files.length) {
            var message;
            
            if (this.uploadFail !== 0) {
                message = Lang.page_editmod_error_multi_upload_failure
                    .replace('X', self.uploadSuccess)
                    .replace('X', self.uploadFail);
            } else {
                message = Lang.page_editmod_success_multi_upload.replace('X', self.uploadSuccess);
            }
            
            self.setFinishedState(self, this.uploadFail === 0, message);
            return;
        }
        
        var file = self.files[self.fileIndex];
        
        try {
            self.fileUpload = new FileUpload(
                file,
                self.type,
                self.$editModForm.data('create-upload-slot-action'),
                self.$editModForm.data('upload-file-chunk-action'),
                self.$editModForm.data('chunk-size'),
                function(response){self.handleFileUploadDone(self, response);},
                function(percentage){self.handleUploadProgress(self, percentage);},
                function(){self.handleRetryExceeded(self);}
            );
        } catch (e) {
            if (e === 404) {
                self.setFinishedState(self, false, Lang.global_chunk_upload_unsupported);
            } else {
                throw e;
            }
        }
    }
    
    /**
     * Handle file upload processing
     * 
     * @param {MultiUpload} self     The MultiUpload instance
     * @param {object}      response The server response
     * @returns {undefined}
     */
    handleFileUploadDone(self, response) {
        var status = false;
        var message = null;
        
        if (typeof response === 'object') {
            status = response.success;
            
            if (status === false) {
               message = response.message;
            }
        }
        
        self.setFileStatus(self, status, message);
        
        if (status) {
            this.uploadCallback(response);
        }
        
        self.handleNextFile(self);
    }
    
    /**
     * Update the upload progress with the percentage (both general and current file)
     * 
     * @param {MultiUpload} self       The MultiUpload instance
     * @param {number}      percentage The uploaded percentage for the current file
     * @returns {undefined}
     */
    handleUploadProgress(self, percentage){
        // Calculate the uploaded file size
        var uploadedSize = 0;
        
        // Add previous completed files
        for (var i = 0; i < self.fileIndex; i++) {
            uploadedSize += self.files[i].size;
        }
        
        // Add current file uploaded size
        uploadedSize += Math.floor(self.files[self.fileIndex].size * percentage / 100);
        
        // Calculate and set global percentage
        var globalPercentage = Math.floor(uploadedSize * 100 / self.totalSize);
        self.$progressBar.css('width', globalPercentage + '%');
        
        // Set local percentage
        var $progressBar = self.fileStatus[self.fileIndex].find('div.progress-bar');
        $progressBar.css('width', percentage + '%');
    }
    
    /**
     * Handle upload failure due to failed retry attempts
     * 
     * @param {MultiUpload} self The MultiUpload instance
     * @returns {undefined}
     */
    handleRetryExceeded(self) {
        this.setFileStatus(this, false, Lang.page_editmod_error_file_upload_retry);
        self.handleNextFile(self);
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
     * @param {object} retryExceededCallback Callback to be executed when the upload retries are exceeded
     * @returns {FileUpload}
     * @throws {404} Missing technical support from browser
     */
    constructor(file, type, slotUrl, chunkUrl, chunkSize, doneCallback, progressCallback, retryExceededCallback) {
        // Check for browser support
        if (typeof FormData === 'undefined') {
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
        
        // XHR
        this.xhr = null;
        this.aborted = false;
        
        this.uploadFile(this);
    }
    
    /**
     * Abort the upload
     * TODO: Check and test that it really works properly
     * 
     * @returns {undefined}
     */
    abort() {
        this.aborted = true;
        
        if (this.xhr !== null) {
            this.xhr.abort();
        }
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
            dataType: 'json',
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                self.xhr = xhr;
                return xhr;
            }
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
        .fail(self.doneCallback);
    }
    
    /**
     * Prepare the next chunk for upload
     * 
     * @param {FileUpload} self The FileUpload instance
     * @returns {undefined}
     */
    processNextChunk(self) {
        // Stop if aborted
        if (this.aborted === true) {
            return;
        }
        
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
                
                self.xhr = xhr;
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
