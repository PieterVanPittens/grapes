
function toastSuccess(result) {
	var messageString = result.message;

	var titleString;
	if (result.pointsUpdate > 0) {
		titleString = "+" + result.pointsUpdate;
	}
	
	toastr.success(messageString, titleString);

	if (result.pointsUpdate > 0) {
		var points = $("#points");
		var pointsupdate = $("#pointsupdate");
		pointsupdate.html("+" + result.pointsUpdate);
		points.hide(0, "linear", function () {
			pointsupdate.show(0, function () {
				pointsupdate.delay(400);
				pointsupdate.hide(0, function () {
					points.html(result.pointsNewTotal);
					points.show(0);
				});
			});
		});
	}
}

function toastError(result) {
	var messageString = result.data.message;
	var titleString = result.status + " - " + result.statusText;

	toastr.error(messageString, titleString);
}

/**
 * renders link to an issue
 * @param issue
 */
function renderIssueLink(issue) {
	var icon = "";
	
	if (issue.issueTypeId == 1) {
		icon = "flash";
	} else if (issue.issueTypeId == 2) {
		icon = "film";
	} else {
		icon = "fire";
	}
	var link = '<span class="glyphicon glyphicon-' + icon + '" aria-hidden="true"></span>&nbsp;<a href="#/issue/' + issue.issueNr + '">' + issue.issueNr +':' + issue.subject + '</a>';
	return link;
}


/**
 * shows toast for data returned from ajax request
 * @param data
 */
/*
function toast(validationState) {
	var messageString = "";
	var addNewLine = false;
	$.each(validationState.messages, function(propertyName, property){
		if (addNewLine) {
			messageString += "<br/>";
		}
		messageString += property.message;
		addNewLine = true;
	});

	
	if ((validationState.validationStateType == 3) && (validationState.points.update > 0)) {
		messageString += "<br/><span style='font-weight: bold; font-size: 1.1em'>+" + validationState.points.update + "</span></p>";
	}
	
	toastr.options = {
			  "closeButton": false,
			  "debug": false,
			  "newestOnTop": true,
			  "progressBar": false,
			  "positionClass": "toast-top-center",
			  "preventDuplicates": false,
			  "onclick": null,
			  "showDuration": "300",
			  "hideDuration": "1000",
			  "timeOut": "5000",
			  "extendedTimeOut": "1000",
			  "showEasing": "swing",
			  "hideEasing": "linear",
			  "showMethod": "fadeIn",
			  "hideMethod": "fadeOut"
			}
	
	
	
	
	
    if (validationState.validationStateType == 2) {
    	// warning
        toastr.warning(messageString);
    } else if (validationState.validationStateType == 3) {
    	// success
        toastr.success(messageString);
    } else {
    	// error
        toastr.error(messageString);    	
    }
    
}
*/
