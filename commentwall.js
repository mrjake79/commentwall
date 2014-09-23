/**
 * Setup.
 */
jQuery(function() {
    // Form handling
    jQuery('#commentForm').submit(handleForm);

    // Sorting change
    jQuery('#changeSort').click(changeSorting);
});

/**
 * Handle form submission.
 */
function handleForm(ev)
{
    // Prevent the default action
    ev.preventDefault();

    // Hide any current errors
    jQuery('.error').hide();

    // Get form elements
    var name = jQuery('#nameInput').val().trim();
    var email = jQuery('#emailInput').val().trim();
    var website = jQuery('#websiteInput').val().trim();
    var comment = jQuery('#commentInput').val().trim();

    // Check for required variables
    var error = false;
    if(name == '') {
        jQuery('#nameError').text('Please provide your name.').show();
        error = true;
    }
    if(comment == '') {
        jQuery('#commentError').text('The comment must not be blank.').show();
        error = true;
    }

    // Handle empty variables
    if(email == '') email = null;
    if(website == '') website = null;
    
    // Display an error message
    if(error) {
        alert('There were one or more errors.  Please correct them and try again.');
        return false;
    }

    // Build the post variables
    var post = {
        'name': name,
        'email': email,
        'website': website,
        'comment': comment,
        'json': true
    };

    // Submit the comment and check for errors
    if(!testMode) {
        // Send the request
        jQuery.post(document.location.href, post, function(ret) {
            if(typeof(ret) == 'object') {
                ret.submitted = new Date(ret.submitted);
                displayComment(ret);
            } else {
                alert(ret);
            }
        });
    } else {
        // Use the current timestamp for submission time
        post.submitted = new Date();

        // Display the comment
        displayComment(post);
    }
}

/**
 * Display a comment.
 */
function displayComment(comment)
{
    // Extract the information
    var name = comment.name;
    var email = comment.email;
    var website = comment.website;
    var submitted = comment.submitted;
    var comment = comment.comment;

    // Hide the message about there being no comments
    jQuery('.noComments').hide();

    // Start the HTML
    var html = "<div class='comment'>";

    // Add the gravatar
    if(email) {
        // Get the hash.  The email has already been trimmed.
        var hash = jQuery.md5(email.toLowerCase());

        // Add the image tag
        html += '<img class="gravatar" src="//www.gravatar.com/avatar/' + hash + '"></img>';
    }

    // Add the website or email link
    html += '<h2>';
    if(website != null) {
        html += '<a target="_blank" href="' + escapeValue(website) + '">';
    } else if(email != null) {
        html += '<a href="mailto:' + escapeValue(email) + '">';
    }

    // Name
    html += escapeValue(name);

    // Close the link and header
    if(website != null || email != null) {
        html += '</a>';
    }
    html += '</h2>';

    // Display submission time
    html += '<div class="submissionTime">' + jQuery.formatDateTime("DD, MM d, yy '@' g:i a", submitted) + '</div>';

    // Display the comment
    html += '<div class="commentText">' 
        + escapeValue(comment).replace(/\r?\n/g, '<br />') 
        + '</div></div>';

    // Display the comment
    if(curSort == 'desc') {
        jQuery('#commentWall').prepend(html);
    } else {
        jQuery('#commentWall').append(html);
    }

    // Clear only the comment field so the same comment isn't posted twice.
    jQuery('#commentInput').val('');
}


/**
 * Encode HTML entities to avoid scripting attacks, etc.
 */
function escapeValue(value)
{
    if(value == null) value = '';
    value = value.replace(/&/g, '&amp;');
    value = value.replace(/</g, '&lt;');
    value = value.replace(/>/g, '&gt;');
    value = value.replace(/"/g, '&quot;');
    return value;
}


/**
 * Change the sorting of the comments.
 */
function changeSorting(ev)
{
    // Don't follow the link
    ev.preventDefault();

    // Change the sorting by moving each one in succession to the front
    var wall = jQuery('#commentWall');
    jQuery('div.comment').each(function() {
        wall.prepend(this);
    });

    // Change the link URL and text
    jQuery('#changeSort')
        .attr('href', '?sort=' + (curSort == 'desc' ? 'asc' : 'desc'))
        .text('Show ' + (curSort == 'desc' ? 'Oldest' : 'Newest') + ' Comments First');
    curSort = (curSort == 'desc' ? 'asc' : 'desc');
}
