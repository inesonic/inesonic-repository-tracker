 /**********************************************************************************************************************
 * Copyright 2021 - 2022, Inesonic, LLC
 *
 * GNU Public License, Version 3:
 *   This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 *   License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any
 *   later version.
 *
 *   This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 *   warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 *   details.
 *
 *   You should have received a copy of the GNU General Public License along with this program.  If not, see
 *   <https://www.gnu.org/licenses/>.
 ***********************************************************************************************************************
 * \file settings-page.js
 *
 * JavaScript module that manages the repository tracker settings page.
 */

/***********************************************************************************************************************
 * Script scope locals:
 */

/**
 * Regular expression used to check a URL.
 *
 * The regular expression below was kindly taken from https://gist.github.com/dperini/729294 and used under the terms
 * of the MIT license.  Credit for the regular expression should go to Diego Perini.
 */
let webUrlRegex = new RegExp(
  "^" +
    // protocol identifier (optional)
    // short syntax // still required
    "(?:(?:(?:https?|ftp):)?\\/\\/)" +
    // user:pass BasicAuth (optional)
    "(?:\\S+(?::\\S*)?@)?" +
    "(?:" +
      // IP address exclusion
      // private & local networks
      "(?!(?:10|127)(?:\\.\\d{1,3}){3})" +
      "(?!(?:169\\.254|192\\.168)(?:\\.\\d{1,3}){2})" +
      "(?!172\\.(?:1[6-9]|2\\d|3[0-1])(?:\\.\\d{1,3}){2})" +
      // IP address dotted notation octets
      // excludes loopback network 0.0.0.0
      // excludes reserved space >= 224.0.0.0
      // excludes network & broadcast addresses
      // (first & last IP address of each class)
      "(?:[1-9]\\d?|1\\d\\d|2[01]\\d|22[0-3])" +
      "(?:\\.(?:1?\\d{1,2}|2[0-4]\\d|25[0-5])){2}" +
      "(?:\\.(?:[1-9]\\d?|1\\d\\d|2[0-4]\\d|25[0-4]))" +
    "|" +
      // host & domain names, may end with dot
      // can be replaced by a shortest alternative
      // (?![-_])(?:[-\\w\\u00a1-\\uffff]{0,63}[^-_]\\.)+
      "(?:" +
        "(?:" +
          "[a-z0-9\\u00a1-\\uffff]" +
          "[a-z0-9\\u00a1-\\uffff_-]{0,62}" +
        ")?" +
        "[a-z0-9\\u00a1-\\uffff]\\." +
      ")+" +
      // TLD identifier name, may end with dot
      "(?:[a-z\\u00a1-\\uffff]{2,}\\.?)" +
    ")" +
    // port number (optional)
    "(?::\\d{2,5})?" +
    // resource path (optional)
    "(?:[/?#]\\S*)?" +
  "$", "i"
);

/***********************************************************************************************************************
 * Functions:
 */

/**
 * Function that checks if a URL is valid.
 *
 * \param url The URL to be checked.
 *
 * \return Returns true if the URL is valid.  Returns false if the URL is invalid.
 */
function inesonicIsValidUrl(url) {
    return webUrlRegex.test(url);
}

/**
 * Function that determines the current number of platform rows.
 *
 * \return Returns the current number of platform rows.
 */
function inesonicNumberRows() {
    let tableBody = document.getElementById("inesonic-repository-tracker-repository-table-body");
    return tableBody.childElementCount;
}

/**
 * Function that determines the row index based on an input field ID.
 *
 * \param[in] id The ID of the input field.
 *
 * \return Returns the row index.
 */
function inesonicGetRowIndex(id) {
    fields = id.split("-");
    return Number(fields[fields.length - 1]);
}

/**
 * Function that checks if we can enable the update button.
 */
function inesonicCheckIfUpdateAllowed() {
    let numberRows    = inesonicNumberRows();
    let updateAllowed = true;
    let nonEmptyRows  = false;

    for (let rowIndex=0 ; rowIndex<numberRows ; ++rowIndex) {
		let packageName = jQuery("#inesonic-repository-tracker-package-name-" + rowIndex).val().trim();
		let projects = jQuery("#inesonic-repository-tracker-projects-" + rowIndex).val().trim();
        let repositoryUrl = jQuery("#inesonic-repository-tracker-repository-url-" + rowIndex).val().trim();
		let description = jQuery("#inesonic-repository-tracker-description-" + rowIndex).val().trim();

        if (packageName || projects || repositoryUrl || description) {
            nonEmptyRows = true;
            updateAllowed = (
                   updateAllowed
				&& packageName
				// && projects - Projects can be empty.
    			&& inesonicIsValidUrl(repositoryUrl)
                && description
            );
        }
    }

	let updateButton = jQuery("#inesonic-repository-tracker-update-repository-table-button");
    if (nonEmptyRows && !updateAllowed) {
		updateButton.addClass("inesonic-disable-click");
    } else {
		updateButton.removeClass("inesonic-disable-click");
    }
}


/**
 * Function that validates a version row.
 *
 * \param[in] rowIndex The row index of the row to be checked.
 */
function inesonicValidateRow(rowIndex) {
    if ((rowIndex + 1) >= inesonicNumberRows()) {
        inesonicAppendRow("", "", "", "");
    }

	let packageName = jQuery("#inesonic-repository-tracker-package-name-" + rowIndex).val().trim();
	let projects = jQuery("#inesonic-repository-tracker-projects-" + rowIndex).val().trim();
    let repositoryUrl = jQuery("#inesonic-repository-tracker-repository-url-" + rowIndex).val().trim();
	let description = jQuery("#inesonic-repository-tracker-description-" + rowIndex).val().trim();

    if (!packageName && !repositoryUrl && !description) {
        jQuery("#inesonic-repository-tracker-package-name-" + rowIndex).removeClass("inesonic-bad-entry");
        jQuery("#inesonic-repository-tracker-repository-url-" + rowIndex).removeClass("inesonic-bad-entry");
        jQuery("#inesonic-repository-tracker-description-" + rowIndex).removeClass("inesonic-bad-entry");
    } else {		
        if (packageName) {
            jQuery("#inesonic-repository-tracker-package-name-" + rowIndex).removeClass("inesonic-bad-entry");
        } else {
            jQuery("#inesonic-repository-tracker-package-name-" + rowIndex).addClass("inesonic-bad-entry");
        }

        if (inesonicIsValidUrl(repositoryUrl)) {
            jQuery("#inesonic-repository-tracker-repository-url-" + rowIndex).removeClass("inesonic-bad-entry");
        } else {
            jQuery("#inesonic-repository-tracker-repository-url-" + rowIndex).addClass("inesonic-bad-entry");
        }

        if (description) {
            jQuery("#inesonic-repository-tracker-description-" + rowIndex).removeClass("inesonic-bad-entry");
        } else {
            jQuery("#inesonic-repository-tracker-description-" + rowIndex).addClass("inesonic-bad-entry");
        }
    }

    inesonicCheckIfUpdateAllowed();
}

/**
 * Function that is triggered when any content changes.
 *
 * \param[in] event The event that triggered the call to this function.
 */
function inesonicContentChanged(event) {
    let rowIndex = inesonicGetRowIndex(event.target.id);
    inesonicValidateRow(rowIndex);
}

/**
 * Function that creates a table data field.
 *
 * \param[in] areaClass  The table data class.
 *
 * \param[in] inputId    The ID for the input field.
 *
 * \param[in] inputClass The input field class.
 *
 * \param[in] inputValue The input value to be placed into the field.
 *
 * \return Returns the table data element to be inserted.
 */
function inesonicCreateTableData(areaClass, inputId, inputClass, inputValue) {
    let dataArea = document.createElement("td");
    dataArea.className = areaClass;

    let inputField = document.createElement("input");
    inputField.type = "text";
    inputField.className = inputClass;
    inputField.id = inputId;
    inputField.value = inputValue;

    dataArea.appendChild(inputField);

    return dataArea;
}

/**
 * Function that binds events to a single version table row.
 *
 * \param[in] rowIndex The zero based row index to bind to.
 */
function inesonicBindEventsToTableRow(rowIndex) {
    jQuery("#inesonic-repository-tracker-package-name-" + rowIndex).on("keyup change paste", inesonicContentChanged);
    jQuery("#inesonic-repository-tracker-projects-" + rowIndex).on("keyup change paste", inesonicContentChanged);
    jQuery("#inesonic-repository-tracker-repository-url-" + rowIndex).on("keyup change paste", inesonicContentChanged);
    jQuery("#inesonic-repository-tracker-description-" + rowIndex).on("keyup change paste", inesonicContentChanged);
}

/**
 * Function that appends a row to the table.
 *
 * \param[in] packageName   The package name to be inserted.
 *
 * \param[in] projects      A string holding the projects.
 *
 * \param[in] repositoryUrl The repository URL.
 *
 * \param[in] description   The description text.
 */
function inesonicAppendRow(packageName, projects, repositoryUrl, description) {
    let tableBody = document.getElementById("inesonic-repository-tracker-repository-table-body");
    let rowIndex = tableBody.childElementCount;

    let tableRow = document.createElement("tr");
    tableRow.className = "inesonic-repository-tracker-repository-table-row";

    tableRow.appendChild(
        inesonicCreateTableData(
            "inesonic-repository-tracker-repository-table-package-name-data",
            "inesonic-repository-tracker-package-name-" + rowIndex,
            "inesonic-repository-tracker-package-name-input",
            packageName
        )
    );

    tableRow.appendChild(
        inesonicCreateTableData(
            "inesonic-repository-tracker-repository-table-projects-data",
            "inesonic-repository-tracker-projects-" + rowIndex,
            "inesonic-repository-tracker-projects-input",
            projects
        )
    );

    tableRow.appendChild(
        inesonicCreateTableData(
            "inesonic-repository-tracker-repository-table-repository-url-data",
            "inesonic-repository-tracker-repository-url-" + rowIndex,
            "inesonic-repository-tracker-repository-url-input",
            repositoryUrl
        )
    );

    tableRow.appendChild(
        inesonicCreateTableData(
            "inesonic-repository-tracker-repository-table-description-data",
            "inesonic-repository-tracker-description-" + rowIndex,
            "inesonic-repository-tracker-description-input",
            description
        )
    );

    tableBody.appendChild(tableRow);

    inesonicBindEventsToTableRow(rowIndex);
}

/**
 * Function that binds key changes to elements.
 */
function inesonicBindEventHandlers() {
    let numberPlatformRows = inesonicNumberRows();

    for (let i=0 ; i<numberPlatformRows ; ++i) {
        inesonicBindEventsToTableRow(i);
    }
}

/**
 * Function that is triggered to update the software version data.
 */
function inesonicUpdateData() {
    let numberRows = inesonicNumberRows();
    let packageData = []
    for (let rowIndex=0 ; rowIndex<numberRows ; ++rowIndex) {
		let packageName = jQuery("#inesonic-repository-tracker-package-name-" + rowIndex).val().trim();
		let projects = jQuery("#inesonic-repository-tracker-projects-" + rowIndex).val().trim();
        let repositoryUrl = jQuery("#inesonic-repository-tracker-repository-url-" + rowIndex).val().trim();
		let description = jQuery("#inesonic-repository-tracker-description-" + rowIndex).val().trim();

		let projectList = projects.split(",").map(function(s) { return s.trim(); });
		
        if (packageName	&& inesonicIsValidUrl(repositoryUrl) && description) {
            packageData.push(
				{
					"name" : packageName,
					"projects" : projectList,
					"url" : repositoryUrl,
					"description" : description
				}
			);
        }
    }

    jQuery.ajax(
        {
            type: "POST",
            url: ajax_object.ajax_url,
            data: {
                "action" : "inesonic_repository_tracker_update",
                "data" : packageData
            },
            dataType: "json",
            success: function(response) {
                if (response !== null) {
                    if (response.status == 'OK') {
						alert("Configuration updated");
					} else {
                        alert("Failed to update repository data: " + response.status);
                    }
                } else {
                    alert("Failed to update repository data.");
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert("Failed to update repository data: " + errorThrown);
            }
        }
    );
}

/***********************************************************************************************************************
 * Main:
 */

jQuery(document).ready(function($) {
    inesonicBindEventHandlers();
    $("#inesonic-repository-tracker-update-repository-table-button").click(inesonicUpdateData);
});
