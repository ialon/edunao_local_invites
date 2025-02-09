define([
    'jquery',
    'core/ajax',
    'core/notification',
    'core/templates',
    'core/str',
], function($, Ajax, Notification, Templates, Str) {
    return {
        init: function(courseID, inviteBody, roles) {
            this.courseID = courseID;
            this.inviteBody = inviteBody;
            this.roles = roles;
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
            let addSuccess = inviteModal.querySelector('#addSuccess');
            let addError = inviteModal.querySelector('#addError');

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

                Ajax.call([{
                    methodname: 'local_invites_check_email',
                    args: {emails: userEmails.value},
                    done: function(data) {
                        data.valid.forEach(result => {
                            if (that.invites.indexOf(result.email) == -1) {
                                // Prepare the context data for the template
                                const context = {
                                    email: result.email,
                                    name: result.name,
                                    roles: that.roles,
                                };
            
                                Templates
                                .render('local_invites/userinvite', context)
                                .done(
                                    function(newUser, js) {
                                        // Append the user to the list
                                        that.invites.push(result.email);
                                        userList.appendChild(document.createRange().createContextualFragment(newUser));
                                        that.updateStatus();

                                        // Add event listener to remove the user
                                        let userElement = userList.querySelector('li:last-child .delete-icon');
                                        userElement.addEventListener('click', function() {
                                            that.invites = that.invites.filter(e => e !== result.email);
                                            that.updateStatus();
                                            this.closest('li').remove();
                                        });
                                    }.bind(this)
                                )
                                .fail(function(ex) {
                                    console.error('Failed to render template', ex);
                                });
                            }
                        });

                        // Notify about results
                        if (data.valid.length > 0) {
                            Str.get_string('validmessage', 'local_invites', data.valid.length)
                            .done(function(message) {
                                this.addSuccess.innerHTML = message;
                                this.addSuccess.classList.remove('hidden');
                                setTimeout(function() {
                                    this.addSuccess.innerHTML = '';
                                    this.addSuccess.classList.add('hidden');
                                }.bind(this), 3000);
                            })
                            .fail(Notification.exception);
                        }
                        if (data.invalid.length > 0) {
                            Str.get_string('invalidmessage', 'local_invites', data.invalid.map(e => e.email).join(', '))
                            .done(function(message) {
                                this.addError.innerHTML = message;
                                this.addError.classList.remove('hidden');
                                setTimeout(function() {
                                    this.addError.innerHTML = '';
                                    this.addError.classList.add('hidden');
                                }.bind(this), 3000);
                            })
                            .fail(Notification.exception);
                        }

                        // Clear the input
                        userEmails.value = '';
                        userEmails.dispatchEvent(new Event('input'));

                        that.updateStatus();
                    }.bind(this),
                    fail: Notification.exception
                }]);
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
