/* 
 * Copyright © 2016-2017 OpenXcom Mod Portal Contributors
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

class NewModManager {
    /**
     * Class initialization
     * 
     * @returns {NewModManager}
     */
    constructor() {
        this.$createModForm = $('form#createMod');
        this.$createModForm.submit(this.handleSubmit);
        
        this.loadingState = false;
        $('div#modNewModal').on('hide.bs.modal', this.handleClose);
    }
    
    /**
     * Handle form submission
     * 
     * @param {event} event The event
     * @returns {undefined}
     */
    handleSubmit(event) {
        event.preventDefault();
        
        newModManager.setLoadingState(true);
        $('input[name="modName"]', newModManager.$createModForm).removeClass('is-invalid');
        $('div.alert', newModManager.$createModForm).addClass('d-none');
        
        $.ajax(newModManager.$createModForm.attr('action'), {
            method: 'post',
            data: newModManager.$createModForm.serialize(),
            dataType: 'json'
        })
        .done(newModManager.handleSubmitDone)
        .fail(newModManager.handleSubmitFail);
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
            window.location.href = data.modUrl;
            return;
        }
        
        $('div.alert', newModManager.$createModForm).text(data.errorMessage).removeClass('d-none');
        $('input[name="modName"]', newModManager.$createModForm).addClass('is-invalid');
        newModManager.setLoadingState(false);
        
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
        newModManager.setLoadingState(false);
        alert('Unexpected error, please try again');
    }
    
    /**
     * Handle modal close
     * 
     * @param {type} event The event
     * @returns {undefined}
     */
    handleClose(event) {
        // Prevent closing the dialog whan waiting for data
        if (newModManager.loadingState === true) {
            event.preventDefault();
        }
    }
    
    /**
     * Set the loading state for the new mod modal
     * 
     * @param {boolean} state The state
     * @returns {undefined}
     */
    setLoadingState(state) {
        newModManager.loadingState = state;
        if (state) {
            $('button', newModManager.$createModForm).attr('disabled','');
            $('div.progress', newModManager.$createModForm).removeClass('d-none');
        } else {
            $('button', newModManager.$createModForm).removeAttr('disabled');
            $('div.progress', newModManager.$createModForm).addClass('d-none');
        }
    }
}

var newModManager = new NewModManager();