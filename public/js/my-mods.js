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

/* global Lang */

/**
 * New mod manager
 */
class NewModManager {
    /**
     * Class initialization
     * 
     * @returns {NewModManager}
     */
    constructor() {
        // Modal and form
        this.$modal = $('div#modNewModal');
        this.$createModForm = $('form#createMod', this.$modal);
        
        // Inputs
        this.$titleInput = $('input[name="modTitle"]', this.$createModForm);
        this.$submitBtn = $('button[type="submit"]', this.$createModForm);
        this.$closeBtn = $('button[type="button"]', this.$createModForm);
        
        // Indicators
        this.$progressBar = $('div.progress', this.$createModForm);
        this.$errorMessage = $('div#error-message', this.$createModForm);
        
        // Handle submit
        this.$createModForm.submit(function(event){
            this.handleSubmit(this, event);
        }.bind(this));
        
        // If the modal can be closed
        this.canClose = true;
        
        // Handle close
        this.$modal.on('hide.bs.modal', function(event){
            this.handleClose(this, event)
        }.bind(this));
    }
    
    /**
     * Handle form submission
     * 
     * @param {NewModManager} self  The NewModManager
     * @param {object}        event The event
     * @returns {undefined}
     */
    handleSubmit(self, event) {
        // Don't do the standard submit
        event.preventDefault();
        
        // Set the proper state
        self.setLoadingState(self, true);
        
        $.ajax(self.$createModForm.attr('action'), {
            method: 'post',
            data: {
                modTitle: self.$titleInput.val()
            },
            dataType: 'json'
        })
        .done(function(data){
            // On success, go to the mod page without further ado
            if (data.success) {
                window.location.href = data.content;
                return;
            }
            
            // On fail, show error
            self.setLoadingState(self, false, data.content);
        })
        .fail(function(){
            // On AJAX failure, show a generic message
            self.setLoadingState(self, false, Lang.global_unexpected_error);
        });
    }
    
    /**
     * Handle modal close
     * 
     * @param {NewModManager} self  The NewModManager
     * @param {object}        event The event
     * @returns {undefined}
     */
    handleClose(self, event) {
        // Prevent closing the dialog when waiting for data
        if (self.canClose === false) {
            event.preventDefault();
        }
    }
    
    /**
     * Set the loading state for the new mod modal
     * 
     * @param {NewModManager} self    The NewModManager
     * @param {boolean}       state   The loading state
     * @param {string}        message The message to display
     * @returns {undefined}
     */
    setLoadingState(self, state, message) {
        // Prevent/allow manual modal close
        self.canClose = !state;
        
        // Properties
        self.$titleInput.prop('disabled', state);
        self.$submitBtn.prop('disabled', state);
        self.$closeBtn.prop('disabled', state);
        
        // Visibility
        if (state) {
            // Loading on
            self.$progressBar.removeClass('d-none');
            self.$errorMessage.addClass('d-none');
        } else {
            // Loading off
            self.$progressBar.addClass('d-none');
            self.$errorMessage.removeClass('d-none').text(message);
        }
    }
}

// Boot it up
$(function(){
    new NewModManager();
});