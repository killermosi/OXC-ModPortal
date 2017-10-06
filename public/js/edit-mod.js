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

class EditModManager {
    /**
     * Handle mod editing
     * 
     * @returns {EditModManager}
     */
    constructor() {
        // Delay before making the slug preview reques, in milliseconds
        this.modSlugPreviewDelay = 500;
        
        this.$editModForm = $('form#editMod');
        this.modSlugPreviewTimer = null;
        
        // Reset the previw timer on every keyup event
        $('input#title', this.$editModForm).keyup(this.resetSlugPreviewTimer);
    }
    
    /**
     * Reset the slug preview timer
     * 
     * @param {event] event The event
     * @returns {undefined}
     */
    resetSlugPreviewTimer(event) {
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
        $.ajax(editModManager.$editModForm.data('slugpreviewaction'), {
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
}

var editModManager = new EditModManager();