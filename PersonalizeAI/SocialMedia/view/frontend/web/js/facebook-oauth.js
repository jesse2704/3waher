define(['jquery'], function ($) {
    'use strict';

    return {
        // Initialize the Facebook SDK with the provided app ID by Webshop owners in the configuration
        init: function (appId) {
            // This function is called once the SDK is loaded
            window.fbAsyncInit = function () {
                FB.init({
                    appId: appId,  // Facebook App ID
                    cookie: true,   // Enable cookies to allow the server to access the session
                    xfbml: true,    // Parse social plugins on this webpage
                    version: 'v16.0' // Use this Graph API version
                });
            };

            // Load the Facebook SDK asynchronously - Create script and append in the head
            (function () {
                if (document.getElementById('facebook-jssdk')) return; // Prevent loading the SDK multiple times
                var script = document.createElement('script');
                script.id = 'facebook-jssdk';
                script.src = 'https://connect.facebook.net/en_US/sdk.js';
                document.getElementsByTagName('head')[0].appendChild(script);
            }());

            // Bind click event to login button
            $('#loginBtn').on('click', function () {
                FB.login(function (response) { // Call FB.login to authenticate user
                    if (response.authResponse) { // Check if login was successful
                        console.log('Welcome! Fetching your information...');
                        this.fetchUserData(); // Fetch user data on successful login
                    } else {
                        console.log('User cancelled login or did not fully authorize.');
                    }

                    
                }.bind(this), { scope: 'public_profile,email,user_friends,user_posts,user_photos,user_videos,user_events,user_likes,user_birthday,user_hometown,user_location,user_link' }); // Request permissions for user data
            }.bind(this));

            // Bind click event to unlink button
            $('#unlinkBtn').on('click', function () {
                console.log("click");
                this.logout(); // Call logout function when unlink button is clicked
            }.bind(this));
        },

        logout: function () {
            FB.getLoginStatus(function (response) {
                if (response.status === 'connected') {
                    // User is logged in and has authorized your app
                    FB.logout(function (logoutResponse) {
                        console.log('User logged out:', logoutResponse);
                        this.clearSessionData(); // Clear session data after logout
                    }.bind(this));
                } else {
                    // User is not logged in or has not authorized your app
                    console.log('User is not logged in to Facebook');
                    this.clearSessionData(); // Clear any remaining session data
                }
            }.bind(this));
        },

        // Function to clear session data on the server and update UI
        clearSessionData: function () {
            $.ajax({
                url: '/facebook/oauth/unlinkfacebook',
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        console.log(response.message);
                        // Clear local session storage
                        sessionStorage.setItem('userId', null);
                        sessionStorage.setItem('FacebookFirstName', null);
                        sessionStorage.setItem('userLastName', null);
                        sessionStorage.setItem('userEmail', null);

                        // Update UI
                        alert('Your Facebook account has been unlinked.');
                        $('#unlinkBtn').prop('disabled', true);
                        $('#loginBtn').prop('disabled', false);
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function () {
                    alert('An error occurred while trying to unlink Facebook.');
                }
            });
        },

        // Fetch user data from Facebook API
        fetchUserData: function () {
            FB.api('/me', {
                fields: 'id,first_name,last_name,name,email,picture.width(2048).height(2048),friends,posts{message,created_time},photos,videos,events,likes,birthday,hometown,location{location{country_code, city}},link,gender,languages,short_name,favorite_teams, age_range'
            }, function (response) {
                console.log('Retrieved user data:', response);
                this.saveUserData(response);

            }.bind(this));
        },

        // Save user data to server via AJAX request
        saveUserData: function (response) {
            $.ajax({
                url: '/facebook/oauth/saveUserData',
                method: 'POST',
                data: {
                    id: response.id,
                    firstname: response.first_name,
                    lastname: response.last_name,
                    name: response.name,
                    email: response.email,
                    profile_pic_url: response.picture.data.url,
                    gender: response.gender,
                    birthday: response.birthday,
                    hometown: response.hometown.name,
                    likes: response.likes ? response.likes : null,
                    location: response.location.location.city,
                    country: response.location.location.country_code,
                    friends: response.friends ? response.friends.data : null,
                    posts: response.posts ? response.posts.data : null,
                    languages: response.languages,
                    favorite_teams: response.favorite_teams,
                    age_range: response.age_range,
                    // payment_subscriptions: response.payment.subscriptions
                },
                success: function (response2) {
                    console.log('Data successfully saved.');

                    $(document).trigger('facebookLinked', [response2]);
                    const lang = (response.languages) ? response.languages[0].name : '';
                    changeStoreView(lang);

                    // location.reload();
                },
                error: function (xhr, status, error) {
                    console.error('Error saving data:', error);
                }
            });
        }
    };
});
