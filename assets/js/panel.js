// Sidebar Toggle
$(document).ready(function(){

    if (!window.localStorage) {
        alert("Sorry, your browser doesn't support localStorage.");
    }

    // Retrieve Elements
    const content = $('#content');
    const sidebar = $('#sidebar');

    // Check if Sidebar Exists
    if(sidebar.length > 0){
        const sidebarCollapse = new bootstrap.Collapse(sidebar, {toggle: false});
        const sidebarToggle = $('#sidebarToggle');

        // Add Event Listener on Toggle
        sidebarToggle.click(function() {
            if (sidebar.hasClass('show')) {
                sidebarToggle.removeClass('active');
                content.css({'width': '100%', 'margin-left': '0px'});
            } else {
                sidebarToggle.addClass('active');
                content.css({'width': 'calc(100% - 300px)', 'margin-left': '300px'});
            }
            sidebarCollapse.toggle();
        });

        // Function to Resize Sidebar
        function resizeSidebar() {
            if ($(window).width() < 768) {
                sidebarToggle.removeClass('active');
                sidebarCollapse.hide();
                content.css({'width': '100%', 'margin-left': '0px'});
            } else {
                sidebarCollapse.show();
                sidebarToggle.addClass('active');
                content.css({'width': 'calc(100% - 300px)', 'margin-left': '300px'});
            }
        }

        // Add Event Listener on Resize
        $(window).resize(function() {
            resizeSidebar();
        });

        // Resize Sidebar
        resizeSidebar();
    }

    $('#footerCredit').click(function(){
        $.ajax({
            url: '/api/core/info',
            type: 'GET',dataType: 'json',
            success: function(response) {
                console.log(response)
                builder.Component(
                    "modal",
                    {
                        onEnter: true,
                        destroy:true,
                        icon: "info-circle",
                        title: builder.Locale.get("About"),
                        cancel: false,
                        submit: false,
                        fullscreen: false,
                        size: "md",
                    },
                    function(modal,component){

                        // Styling
                        component.addClass('modal-blue');
                        component.footer.remove();

                        // Content Container
                        component.body.container = $(document.createElement('div')).attr({
                            "class": "d-flex flex-column justify-content-center align-items-center user-select-none",
                        }).appendTo(component.body);

                        // Logo
                        component.body.container.logo = $(document.createElement('img')).attr({
                            "src": response.logo,
                            "class": "img-fluid",
                            "style": "max-width: 200px; margin-bottom: 20px;",
                        }).appendTo(component.body.container);

                        // Application Name & Version
                        component.body.container.title = $(document.createElement('div')).attr({
                            "class": "d-flex justify-content-center align-items-center",
                        }).appendTo(component.body.container);
                        component.body.container.title.name = $(document.createElement('h2')).text(response.name).appendTo(component.body.container.title);
                        component.body.container.title.version = $(document.createElement('span')).attr({
                            "class": "badge bg-primary ms-2 cursor-pointer",
                        }).text(response.version).appendTo(component.body.container.title);
                        component.body.container.title.version.click(function(){
                            builder.Component(
                                "modal",
                                {
                                    onEnter: true,
                                    destroy:true,
                                    icon: "file-earmark-diff",
                                    title: builder.Locale.get("Changelog"),
                                    cancel: false,
                                    submit: false,
                                    fullscreen: false,
                                    size: "lg",
                                },
                                function(modal,component){

                                    // Styling
                                    component.dialog.css({'max-width': '720px'});
                                    component.addClass('modal-blue');
                                    component.body.addClass('text-bg-dark').css({'border-bottom-left-radius': 'var(--bs-modal-inner-border-radius)','border-bottom-right-radius': 'var(--bs-modal-inner-border-radius)'});
                                    component.footer.remove();

                                    // Add a preformatted text
                                    component.body.container = $(document.createElement('div')).attr({
                                        "class": "vh-70 overflow-y-auto",
                                    }).html(marked.parse(response.changelog)).appendTo(component.body);

                                    // Show Modal
                                    modal.show();
                                }
                            );
                        });

                        // Copyright
                        component.body.container.copyright = $(document.createElement('div')).attr({
                            "class": "d-flex flex-column justify-content-center align-items-center mt-3",
                        }).appendTo(component.body.container);
                        component.body.container.copyright.header = $(document.createElement('strong')).text(builder.Locale.get('Copyright')).appendTo(component.body.container.copyright);
                        component.body.container.copyright.owner = $(document.createElement('span')).html(response.owner + ' ' + builder.Locale.get('All rights reserved') + '.').appendTo(component.body.container.copyright)

                        // Developers
                        component.body.container.developed = $(document.createElement('div')).attr({
                            "class": "d-flex flex-column justify-content-center align-items-center mt-3",
                        }).appendTo(component.body.container);
                        component.body.container.developed.header = $(document.createElement('strong')).text(builder.Locale.get('Developed by')).appendTo(component.body.container.developed);
                        for(const [key, author] of Object.entries(response.authors)){
                            $(document.createElement('a')).attr({
                                "class": "btn btn-link p-0",
                                "href": author.url,
                                "target": "_blank",
                            }).text(author.name).appendTo(component.body.container.developed)
                        }

                        // License
                        component.body.container.license = $(document.createElement('div')).attr({
                            "class": "d-flex flex-column justify-content-center align-items-center mt-3",
                        }).appendTo(component.body.container);
                        component.body.container.license.header = $(document.createElement('strong')).text(builder.Locale.get('License')).appendTo(component.body.container.license);
                        component.body.container.license.button = $(document.createElement('btn')).attr({
                            "class": "btn btn-link p-0",
                        }).text(response.license.type).appendTo(component.body.container.license)
                        component.body.container.license.button.click(function(){
                            builder.Component(
                                "modal",
                                {
                                    onEnter: true,
                                    destroy:true,
                                    icon: "key",
                                    title: response.license.type,
                                    cancel: false,
                                    submit: false,
                                    fullscreen: false,
                                    size: "lg",
                                },
                                function(modal,component){

                                    // Styling
                                    component.dialog.css({'max-width': '720px'});
                                    component.addClass('modal-blue');
                                    component.body.addClass('text-bg-dark').css({'border-bottom-left-radius': 'var(--bs-modal-inner-border-radius)','border-bottom-right-radius': 'var(--bs-modal-inner-border-radius)'});
                                    component.footer.remove();

                                    // Add a preformatted text
                                    component.body.container = $(document.createElement('pre')).attr({
                                        "class": "vh-70",
                                    }).text(response.license.content).appendTo(component.body);

                                    // Show Modal
                                    modal.show();
                                }
                            );
                        });

                        // Show Modal
                        modal.show();
                    }
                );
            }
        });
    });
});

// Theming
$(document).ready(function(){
    // // Light Mode Switcher
    // $('[data-bs-theme-value]').click(function () {
    //     const theme = $(this).attr('data-bs-theme-value');
    //     $('[data-bs-theme]').attr('data-bs-theme', theme);
    //     $('[data-bs-theme-value]').removeClass('active');
    //     $(this).addClass('active');
    // });

    // // Theme Switcher
    // $('[data-theme-value]').click(function () {
    //     const theme = $(this).attr('data-theme-value');
    //     $('[data-theme-value]').removeClass('active');
    //     $(this).addClass('active');
    //     $('link[data-theme]').prop("disabled", true);
    //     $('link[data-theme="'+theme+'"]').prop("disabled", false);
    // });

    // // Default Theme
    // const defaultTheme = $('html[data-theme]').attr('data-theme');
    // $('[data-theme-value]').removeClass('active');
    // $('[data-theme-value="' + defaultTheme + '"]').addClass('active');
    // $('link[data-theme]').prop("disabled", true);
    // $('link[data-theme="'+defaultTheme+'"]').prop("disabled", false);
});

// Control Collapse
$(document).ready(function(){
    $('[data-bs-toggle="collapse"][data-bs-target="#controlsCollapsible"]').click(function () {
        if($(this).attr('aria-expanded') === 'true'){
            $(this).find('i').removeClass('bi-chevron-left').addClass('bi-chevron-right');
        } else {
            $(this).find('i').removeClass('bi-chevron-right').addClass('bi-chevron-left');
        }
    });
});

// Back to Top
$(document).ready(function(){
    $('.back-to-top').hide();
    $('.back-to-top').find('button').click(function(event) {
        $('html, body').animate({scrollTop: 0}, 500);
    });
    window.onscroll = function() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            $('.back-to-top').show();
        } else {
            $('.back-to-top').hide();
        }
    };
    $('.onScroll').each(function(){
        const self = $(this);
        $('.back-to-top').find('button').click(function(event) {
            self.animate({scrollTop: 0}, 500);
        });
        self.scroll(function(){
            if (self.scrollTop() > 20) {
                $('.back-to-top').show();
            } else {
                $('.back-to-top').hide();
            }
        });
    });
});

// Nested Dropdowns
$(document).ready(function(){
    $('.dropdown-menu a[data-bs-toggle="dropdown"], .dropdown-menu button[data-bs-toggle="dropdown"]').on('click', function(e) {
        e.stopPropagation();

        var $el = $(this);
        var $parent = $(this).offsetParent(".dropdown-menu");
        if (!$(this).next().hasClass('show')) {
            $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
        }
        $(this).parent("li").toggleClass('show');

        return false;
    });
    $('body').on('hide.bs.dropdown', '.dropdown', function (e) {
        if ($(this).hasClass('show') && $(e.target).hasClass('dropdown-submenu')) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
});

// Create Historic Breadcrumbs
$(document).ready(function(){

    // Retrieve the breadcrumbs container
    const element = $('#breadcrumbs');

    // Check if the breadcrumbs container exists
    if(element.length === 0) return;

    // Set the breadcrumbs parameters
    const maxItems = 5;

    // Retrieve the breadcrumbs
    var breadcrumbs = JSON.parse(localStorage.getItem('breadcrumbs')) || [];

    // Retrieve the current page information
    const title = $('#pageTitle').text().trim() || $('h1').text().trim() || $('title').text().trim();
    const link = window.location.href;

    // Check if the current page is already in the breadcrumbs
    const index = breadcrumbs.findIndex(item => item.link === link);
    if(index !== -1){

        // Remove the current page from the breadcrumbs
        breadcrumbs.splice(index, 1);
    }

    // Append the current page to the breadcrumbs
    breadcrumbs.push({title: title, link: link});

    // Remove the oldest breadcrumbs if the limit is reached
    if(breadcrumbs.length > maxItems) breadcrumbs.shift();

    // Save the breadcrumbs to local storage
    localStorage.setItem('breadcrumbs', JSON.stringify(breadcrumbs));

    // Insert the breadcrumbs into the container
    breadcrumbs.forEach(function(item, index){

        // Create the breadcrumb item
        const breadcrumbItem = $(document.createElement('li')).attr({
            "class": "breadcrumb-item " + ((index === breadcrumbs.length - 1) ? 'active' : ''),
        }).appendTo(element);

        // Check if the breadcrumb is the last one
        if(index === breadcrumbs.length - 1){

            // Create the breadcrumb item without a link
            breadcrumbItem.text(item.title);
        } else {

            // Create the breadcrumb item with a link
            $(document.createElement('a')).attr({
                "href": item.link,
            }).text(item.title).appendTo(breadcrumbItem);
        }
    });
});

// Set active link
$(document).ready(function(){
    $('a').removeClass('active');
    $('a[href="'+window.location.pathname + window.location.search+'"]').each(function () {
        $(this).addClass('active');
        $(this).parents('.collapse').addClass('show');
        $(this).parents('[data-bs-toggle="collapse"]').attr('aria-expanded',true);
    });
    $('button').each(function () {
        if ($(this).attr('data-route') === window.location.pathname) {
            $(this).addClass('active');
            $(this).parents('.collapse').addClass('show');
            $(this).parents('[data-bs-toggle="collapse"]').attr('aria-expanded',true);
        }
    });
});

// Configure Search
$(document).ready(function(){
    builder.Search.scan();
});

// Configure Toast
$(document).ready(function(){
    builder.Toast.prependTo('body');
    builder.Toast.position('bottom-end');
});

// Set Locale's callback
builder.Locale._callback = function(key, locale){
    $.ajax({
        url: '/api/locale/get?locale='+locale+'&key=' + key,
        type: 'GET',dataType: 'json'
    });
}

// Retrieve the locales
$.ajax({
    url: '/api/locale/translations',
    type: 'GET',dataType: 'json',
    success: function(response){
        builder.Locale.save('en-ca', response);
    },
});
$.ajax({
    url: '/api/locale/current',
    type: 'GET',dataType: 'json',
    success: function(response){
        var locale = response;
        $.ajax({
            url: '/api/locale/translations?locale=' + locale,
            type: 'GET',dataType: 'json',
            success: function(response){
                builder.Locale.save(locale, response);
            },
        });
    },
});

// Retrieve the libraries
$.ajax({
    url: '/api/locale/locales',
    type: 'GET',dataType: 'json',
    success: function(response){
        var locales = [];
        for(const [id, text] of Object.entries(response)){
            locales.push({id: id, text: text});
        }
        builder.Option.save('locales', locales);
    },
});
$.ajax({
    url: '/api/library/fetch',
    type: 'GET',dataType: 'json',
    success: function(response){
        for(const [library, records] of Object.entries(response.options)){
            switch(library){
                case 'states':
                    for(const [target, record] of Object.entries(records)){
                        builder.Option.save(library, record, target);
                    }
                    break;
                default:
                    builder.Option.save(library, records);
                    break;
            }
        }
    },
});

// // Configure Notification
// builder.Notification._properties.callback.readAll = function(){
//     api.post('notification/readall',{}, {success:function(response){}});
// };

// // Handle Notifications
// var statusNotification = true;
// const disableNotification = function(){
//     statusNotification = false;
// }
// const enableNotificatione = function(){
//     statusNotification = true;
// }
// const toggleNotification = function(){
//     statusNotification = !statusNotification;
// }
// let notifications = {};
// const retrieveNotifications = function(){
//     if(!AUTHENTICATED) return;
//     if(!statusNotification) return;
//     api.post('notification/get',{}, {
//         error:function(xhr,status,error){
//             clearInterval(intervalNotifications);
//         },
//         success:function(response){
//             for(const [id, notification] of Object.entries(response)){
//                 if(typeof notifications[id] === 'undefined'){
//                     builder.Notification.add(
//                         {
//                             label: builder.Parser.parse(notification.label),
//                             icon: notification.icon,
//                             color: notification.color,
//                             isRead: !!notification.isRead,
//                             click: function(item,component){
//                                 api.post('notification/read',{id:id}, {success:function(response){
//                                     if(notification.link){
//                                         window.location.href = notification.link;
//                                     }
//                                 }});
//                             },
//                             onRead: function(item,component){
//                                 api.post('notification/read',{id:id}, {success:function(response){
//                                     item.read();
//                                 }});
//                             },
//                         },
//                         function(item){
//                             notifications[id] = {item: item, notification: notification};
//                         },
//                     );
//                 } else {
//                     const item = notifications[id].item;
//                     notifications[id] = {item: item, notification: notification};
//                     if(!!notification.isRead){
//                         item.read();
//                     }
//                 }
//             }
//         },
//     });
// };
// retrieveNotifications();
// const intervalNotifications = setInterval(function(){
//     retrieveNotifications();
// }, 20000);

// // Configure Message
// builder.Message._properties.callback.viewAll = function(){
//     window.location.href = '/messages';
// };

// // Handle Message
// var statusMessage = true;
// const disableMessage = function(){
//     statusMessage = false;
// }
// const enableMessage = function(){
//     statusMessage = true;
// }
// const toggleMessage = function(){
//     statusMessage = !statusMessage;
// }
// let messages = {};
// const retrieveMessages = function(){
//     if(!AUTHENTICATED) return;
//     if(!statusMessage) return;
//     api.post('message/get',{}, {
//         error:function(xhr,status,error){
//             clearInterval(intervalMessages);
//         },
//         success:function(response){
//             for(const [id, message] of Object.entries(response)){
//                 if(typeof messages[id] === 'undefined'){

//                     // Set contact
//                     let contact = JSON.parse(message.from)[0];

//                     // Add the message
//                     builder.Message.add(
//                         {
//                             label: message.subject,
//                             name: contact,
//                             email: contact,
//                             datetime: message.date,
//                             isRead: !!message.isRead,
//                             click: function(item,component){
//                                 item.read(function(){
//                                     if(message.link){
//                                         window.location.href = message.link;
//                                     }
//                                 });
//                             },
//                             onRead: function(item,component){
//                                 api.post('message/read',{mid:message.mid}, {success:function(response){}});
//                             },
//                         },
//                         function(item){
//                             messages[id] = {item: item, message: message};
//                         },
//                     );
//                 } else {
//                     const item = messages[id].item;
//                     messages[id] = {item: item, message: message};
//                     if(!!message.isRead){
//                         item.read();
//                     }
//                 }
//             }
//         },
//     });
// };
// retrieveMessages();
// const intervalMessages = setInterval(function(){
//     retrieveMessages();
// }, 20000);

// // Configure Task
// builder.Task._properties.callback.viewAll = function(){
//     window.location.href = '/tasks';
// };

// // Handle Tasks
// var statusTask = true;
// const disableTask = function(){
//     statusTask = false;
// }
// const enableTask = function(){
//     statusTask = true;
// }
// const toggleTask = function(){
//     statusTask = !statusTask;
// }
// let tasks = {};
// const retrieveTasks = function(){
//     if(!AUTHENTICATED) return;
//     if(!statusTask) return;
//     api.post('task/get',{}, {
//         error:function(xhr,status,error){
//             clearInterval(intervalTasks);
//         },
//         success:function(response){
//             for(const [id, task] of Object.entries(response)){
//                 if(typeof tasks[id] === 'undefined'){
//                     if(task.isActive){
//                         builder.Task.add(
//                             {
//                                 label: builder.Parser.parse(task.label),
//                                 progress: {
//                                     scale: task.scale,
//                                     color: task.color,
//                                 },
//                                 click: function(item,component){
//                                     if(task.link){
//                                         window.location.href = task.link;
//                                     }
//                                 },
//                             },
//                             function(item){
//                                 item.set(task.progress);
//                                 tasks[id] = {item: item, task: task};
//                             },
//                         );
//                     }
//                 } else {
//                     const item = tasks[id].item;
//                     tasks[id] = {item: item, task: task};
//                     item.set(task.progress);
//                 }
//             }
//         },
//     });
// }
// retrieveTasks();
// const intervalTasks = setInterval(function(){
//     retrieveTasks();
// }, 20000);

// // Add General Button Events
// $(document).ready(function(){

//     // Retrieve the current URL
//     const url = window.location.href;

//     // Wait for the page to load
//     setTimeout(function(){

//         // Configure the subscribe buttons
//         $('[data-action="subscribe"]').each(function(){

//             // Retrieve the button and user
//             const button = $(this);
//             const icon = button.find('i');
//             const user = button.attr('data-user');

//             // Check if the user is set
//             if(!user) return;

//             // Retrieve the current subscription status
//             api.post('subscription/current',{link: url.split('&')[0], user: user}, {success:function(response){

//                 // Set the button attributes
//                 button.attr('data-subscribed', response);

//                 // Check if the user is subscribed
//                 if(response > 0){
//                     button.text('Unsubscribe').prepend($(document.createElement('i')).addClass('bi bi-bell-slash me-1'));
//                 } else {
//                     button.text('Subscribe').prepend($(document.createElement('i')).addClass('bi bi-bell me-1'));
//                 }
//             }});

//             // Add the click event
//             button.click(function(){

//                 // Retrieve the current subscription status
//                 const subscribed = button.attr('data-subscribed');

//                 // Check if the user is subscribed
//                 if(subscribed > 0){
//                     api.post('subscription/unsubscribe',{link: url.split('&')[0], user: user}, {success:function(response){
//                         button.attr('data-subscribed', 0);
//                         button.text('Subscribe');
//                         icon.removeClass('bi-bell-slash').addClass('bi-bell').prependTo(button);
//                     }});
//                 } else {
//                     api.post('subscription/subscribe',{link: url.split('&')[0], user: user}, {success:function(response){
//                         button.attr('data-subscribed', 1);
//                         button.text('Unsubscribe');
//                         icon.removeClass('bi-bell').addClass('bi-bell-slash').prependTo(button);
//                     }});
//                 }
//             });
//         });

//         // Configure the wave buttons
//         $('[data-action="wave"]').each(function(){

//             // Retrieve the button and user
//             const button = $(this);
//             const icon = button.find('i');
//             const relationship = JSON.parse(button.attr('data-relationship') ?? '[]');

//             // Check if the relationship is set
//             if(Object.entries(relationship).length <= 0) return;

//             // Add the click event
//             button.click(function(){

//                 // Fetch Colleagues
//                 api.post('user/colleagues', {cache:true,success:function(response){

//                     // Constants
//                     const Colleagues = response;

//                     // Create a Modal
//                     builder.Component(
//                         "modal",
//                         {
//                             onEnter: true,
//                             destroy:true,
//                             icon: "person-raised-hand",
//                             title: builder.Locale.get("Wave Someone"),
//                             cancel: true,
//                             submit: true,
//                             size: 'lg',
//                             callback: {
//                                 submit: function(element,modal){
//                                     element.form.submit();
//                                 },
//                             },
//                         },
//                         function(modal,component){

//                             // Save Modal Component for select2 fields
//                             const componentModal = component;

//                             // Set colors to the modal's header
                                // component.addClass('modal-purple');

//                             // Change the label of the submit button
//                             component.footer.submit.text(builder.Locale.get('Wave')).addClass('btn-purple').removeClass('btn-link');
//                             component.footer.submit.icon = $(document.createElement('i')).addClass('bi bi-person-raised-hand me-1').prependTo(component.footer.submit);

//                             // Create the form
//                             component.form = builder.Component(
//                                 "form",
//                                 component.body,
//                                 {
//                                     class:{
//                                         form: 'row row-cols-3',
//                                         field: null,
//                                     },
//                                     callback: {
//                                         submit: function(form){

//                                             // Retrieve Values
//                                             const Values = form.val();

//                                             // API Request
//                                             api.post('wave/new',{link: window.location.href, user: Values.user}, {success:function(response){

//                                                 // Close the modal
//                                                 modal.hide();
//                                             }});
//                                         },
//                                     },
//                                 },
//                                 function(form,component){

//                                     // User
//                                     form.add(
//                                         {
//                                             name: 'user',
//                                             label: builder.Locale.get('Colleague'),
//                                             icon: 'person-badge',
//                                             type: 'select',
//                                             options: Colleagues,
//                                             modal: componentModal,
//                                         },
//                                         function(input){
//                                             $(document.createElement('div')).addClass('col-12').html(input).appendTo(component);
//                                         },
//                                     );
//                                 },
//                             );

//                             // Show the modal
//                             modal.show();
//                         },
//                     );
//                 }});
//             });
//         });
//     }, 1000);
// });

// // Check User Activity
// $(document).ready(function() {
//     var isActive;

//     $(window).focus(function() {
//         isActive = true;
//     });

//     $(window).blur(function() {
//         isActive = false;
//     });

//     // Check the isActive variable every 1 second
//     setInterval(function(){
//         if (isActive) {
//             console.log("User is active");
//         } else {
//             console.log("User is inactive");
//         }
//     }, 1000);
// });

