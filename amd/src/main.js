define([
    'jquery',
    'core/ajax',
    'core/notification',
    'core/templates',
    'core/str',
], function($, Ajax, Notification, Templates, Str) {
    return {
        init: function(courseID, inviteBody, roles, remaining) {
            this.courseID = courseID;
            this.inviteBody = inviteBody;
            this.roles = roles;
            this.remaining = remaining;
            this.invites = [];

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', this.addInviteListener.bind(this));
            } else {
                this.addInviteListener();
            }
        },
        addInviteListener: function() {
            let that = this;

            document.addEventListener('shareModalLoaded', function() {
                let inviteUsers = document.getElementById('openInviteUsers');

                // Prevent default action and show modal.
                inviteUsers.addEventListener('click', (event) => {
                    event.preventDefault();
                    that.showModal();
                });
            });
        },
        showModal: function() {
            // Check if the modal already exists
            let existingModal = document.getElementById('inviteModal');
            if (existingModal) {
                $('#inviteModal').modal('show');
                document.querySelector('.modal-backdrop:last-of-type').setAttribute('style', 'z-index: 1060;');
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
                document.querySelector('.modal-backdrop:last-of-type').setAttribute('style', 'z-index: 1060;');

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
            let inviteUsers = inviteModal.querySelector('#inviteUsers');

            // Check if input is empty to enable/disable add user button
            userEmails.addEventListener('input', function() {
                let currentRemaining = this.remaining - this.invites.length;

                // Enable/disable the add user button
                addUser.disabled = false;
                if (currentRemaining > 0) {
                    if (userEmails.value.trim() === '') {
                        addUser.disabled = true;
                    }
                }
            });

            // Click add user button
            addUser.addEventListener('click', function(event) {
                event.preventDefault();

                Ajax.call([{
                    methodname: 'local_invites_check_email',
                    args: {
                        courseid: that.courseID,
                        emails: userEmails.value
                    },
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

            // Click send invites button
            inviteUsers.addEventListener('click', function(event) {
                event.preventDefault();

                let invitations = [];
                let users = userList.querySelectorAll('li');
                users.forEach(user => {
                    let email = user.getAttribute('data-email');
                    let roleid = user.querySelector('select').value;
                    invitations.push({email: email, roleid: parseInt(roleid, 10)});
                });

                Ajax.call([{
                    methodname: 'local_invites_send_invites',
                    args: {
                        courseid: that.courseID,
                        invitations: invitations,
                        message: inviteModal.querySelector('#inviteMessage').value,
                    },
                    done: function(data) {
                        if (data.success) {
                            Str.get_string('invitationssent', 'local_invites')
                            .done(function(message) {
                                this.addSuccess.innerHTML = message;
                                this.addSuccess.classList.remove('hidden');
                                setTimeout(function() {
                                    this.addSuccess.innerHTML = '';
                                    this.addSuccess.classList.add('hidden');

                                    // Empty the user list and reset the invites array
                                    userList.innerHTML = '';
                                    that.invites = [];
        
                                    // Close the modal
                                    $('#inviteModal').modal('hide');
                                    that.updateStatus();
                                }.bind(this), 3000);
                            })
                            .fail(Notification.exception);
                        } else {
                            this.addError.innerHTML = data.message;
                            this.addError.classList.remove('hidden');
                            setTimeout(function() {
                                this.addError.innerHTML = '';
                                this.addError.classList.add('hidden');
                            }.bind(this), 3000);
                        }

                        that.updateStatus();
                    }.bind(this),
                    fail: Notification.exception
                }]);
            });

            that.updateStatus();
        },
        updateStatus: function() {
            let remainingInvites = inviteModal.querySelector('.remaining-invites');
            let inviteDetails = inviteModal.querySelector('#inviteDetails');
            let inviteUsers = inviteModal.querySelector('#inviteUsers');
            let addUser = inviteModal.querySelector('#addUser');

            let invites = this.invites.length;
            let currentRemaining = this.remaining - invites;

            // Update the remaining invites message
            Str.get_string('remaininginvites', 'local_invites', currentRemaining)
            .done(function(message) {
                remainingInvites.innerHTML = message;
            })
            .fail(Notification.exception);

            // Enable/disable the add user button
            if (currentRemaining == 0) {
                addUser.disabled = true;
            } else {
                addUser.disabled = false;
            }

            // Show/hide the invite details and invite users button
            if (invites === 0) {
                inviteDetails.classList.add('hidden');
                inviteUsers.disabled = true;
            } else {
                inviteDetails.classList.remove('hidden');
                inviteUsers.disabled = false;
            }
        }
    };
});
