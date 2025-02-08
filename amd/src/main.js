define([
    'jquery',
    'core/ajax',
    'core/templates',
    'core/str',
], function($, Ajax, Templates, str) {
    return {
        init: function(courseID, inviteBody) {
            this.courseID = courseID;
            this.inviteBody = inviteBody;
            this.invites = [];

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', this.moveMenuItem.bind(this));
            } else {
                this.moveMenuItem();
            }
        },
        moveMenuItem: function() {
            let inviteItemLink = document.querySelector('li[data-key="inviteusers"] a');
            if (! inviteItemLink) {
                return;
            }

            // Clone the invite link.
            let inviteItemClone = inviteItemLink.cloneNode(true);
            // Remove role and tabindex attributes
            inviteItemClone.removeAttribute('role');
            inviteItemClone.removeAttribute('tabindex');
            // Modify the cloned link to be an info button with the fa-user-plus icon.
            inviteItemClone.classList.remove('dropdown-item');
            inviteItemClone.classList.add('btn', 'btn-sm', 'btn-info', 'd-flex', 'align-items-center', 'ml-3');
            inviteItemClone.style.height = 'fit-content';
            inviteItemClone.innerHTML = '<i class="fa fa-user-plus mr-2"></i> Send invites';

            // Find the course title element and insert the cloned link next to it.
            let courseTitle = document.querySelector('.page-header-headings');
            if (courseTitle) {
                courseTitle.parentNode.insertBefore(inviteItemClone, courseTitle.nextSibling);
            }

            // Prevent default action and show modal.
            inviteItemClone.addEventListener('click', (event) => {
                event.preventDefault();
                this.showModal();
            });
            // Prevent default action and show modal.
            inviteItemLink.addEventListener('click', (event) => {
                event.preventDefault();
                this.showModal();
            });
        },
        showModal: function() {
            // Check if the modal already exists
            let existingModal = document.getElementById('inviteModal');
            if (existingModal) {
                $('#inviteModal').modal('show');
                return;
            }

            // Prepare the context data for the template
            const context = {
                courseID: this.courseID,
                inviteBody: this.inviteBody,
            };

            Templates.render('local_invites/modal', context).done(function(html, js) {
                // Append the modal to the body
                document.body.insertAdjacentHTML('beforeend', html);
                Templates.runTemplateJS(js);
                $('#inviteModal').modal('show');

                // Add modal event listeners
                this.addModalListeners();
            }.bind(this)).fail(function(ex) {
                console.error('Failed to render template', ex);
            });
        },
        addModalListeners: function() {
            let that = this;
            let inviteModal = document.getElementById('inviteModal');
            let userEmails = inviteModal.querySelector('#userEmails');
            let addUser = inviteModal.querySelector('#addUser');
            let userList = inviteModal.querySelector('#userList');

            // Check if input is empty to enable/disable add user button
            userEmails.addEventListener('input', function() {
                if (userEmails.value.trim() === '') {
                    addUser.disabled = true;
                } else {
                    addUser.disabled = false;
                }
            });

            // Click add user button
            addUser.addEventListener('click', function(event) {
                event.preventDefault();
                
                // Get the emails and split them by comma or semicolon
                let emails = userEmails.value.split(/,|;/);
                
                userEmails.value = '';
                userEmails.dispatchEvent(new Event('input'));

                emails.forEach(email => {
                    email = email.trim().toLowerCase();

                    if (that.invites.indexOf(email) == -1) {
                        // Prepare the context data for the template
                        const context = {
                            name: "John Doe",
                            email: email,
                        };
    
                        Templates.render('local_invites/userinvite', context).done(function(newUser, js) {
                            // Append the user to the list
                            that.invites.push(email);
                            that.updateStatus();
                            userList.appendChild(document.createRange().createContextualFragment(newUser));

                            // Add event listener to remove the user
                            let userElement = userList.querySelector('li:last-child .delete-icon');
                            userElement.addEventListener('click', function() {
                                that.invites = that.invites.filter(e => e !== email);
                                that.updateStatus();
                                this.closest('li').remove();
                            });
                        }.bind(this)).fail(function(ex) {
                            console.error('Failed to render template', ex);
                        });
                    }
                });
                
            });
        },
        updateStatus: function() {
            let inviteDetails = inviteModal.querySelector('#inviteDetails');
            let inviteUsers = inviteModal.querySelector('#inviteUsers');

            if (this.invites.length === 0) {
                inviteDetails.classList.add('hidden');
                inviteUsers.disabled = true;
            } else {
                inviteDetails.classList.remove('hidden');
                inviteUsers.disabled = false;
            }
        }
    };
});
