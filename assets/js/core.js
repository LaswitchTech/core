// Wait for the DOM to be fully loaded before executing the script
document.addEventListener('DOMContentLoaded', () => {
    (() => {

        // Enable Theme toggling
        (() => {
            // Add event listeners to all theme toggle buttons
            document.querySelectorAll('[data-bs-theme-value]')
                .forEach(themeToggle => {
                    themeToggle.addEventListener('click', () => {
                        const theme = themeToggle.getAttribute('data-bs-theme-value');
                        document.documentElement.setAttribute('data-bs-theme', theme);
                        localStorage.setItem('theme', theme);
                        document.querySelectorAll('[data-bs-theme-value]')
                            .forEach(btn => {
                                btn.classList.toggle('active', btn.getAttribute('data-bs-theme-value') === theme);
                                if (btn.getAttribute('data-bs-theme-value') === theme) {
                                    btn.querySelector('i.bi-check2').classList.remove('d-none');
                                } else {
                                    btn.querySelector('i.bi-check2').classList.add('d-none');
                                }
                            });
                    });
                });

            // Set initial theme based on localStorage or default
            const savedTheme = localStorage.getItem('theme') || 'auto';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            document.querySelectorAll('[data-bs-theme-value]')
                .forEach(btn => {
                    btn.classList.toggle('active', btn.getAttribute('data-bs-theme-value') === savedTheme);
                    if (btn.getAttribute('data-bs-theme-value') === savedTheme) {
                        btn.querySelector('i.bi-check2').classList.remove('d-none');
                    }
                });
        })();

        // Back to Top
        (() => {
            const $btn = $('.back-to-top').hide();
            if (!$btn.length) return;

            // Prefer an explicit .onScroll container if present,
            // otherwise fall back to the root/window.
            const $candidate = $('.onScroll').eq(0);
            const $container = $candidate.length ? $candidate : $(document.scrollingElement || document.documentElement);

            // Treat html/body/root as "window scrolling"
            const el = $container[0];
            const isRoot = !el || el === document || el === document.body || el === document.documentElement || el === document.scrollingElement;

            const getScrollTop = () => (isRoot ? $(window).scrollTop() : $container.scrollTop());

            const update = () => {
                const top = getScrollTop();
                // console.log('scrollTop:', top); // uncomment to debug
                if (top > 20) $btn.show(); else $btn.hide();
            };

            // Ensure we don't stack handlers if this block runs twice
            $btn.off('click.backtotop').on('click.backtotop', (e) => {
                e.preventDefault();
                if (isRoot) {
                    // cross‑engine smooth scroll: animate both html & body
                    $('html, body').stop(true).animate({ scrollTop: 0 }, 500);
                } else {
                    $container.stop(true).animate({ scrollTop: 0 }, 500);
                }
            });

            if (isRoot) {
                $(window).off('scroll.backtotop resize.backtotop').on('scroll.backtotop resize.backtotop', update);
            } else {
                $container.off('scroll.backtotop').on('scroll.backtotop', update);
            }

            update(); // set initial state
        })();

        // Set active link
        (() => {
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
        })();

        // Sidebar Toggle
        (() => {
            const app = document.querySelector('#app');
            const toggleBtn = document.querySelector('#sidebarToggle');
            const closeBtn = document.querySelector('#sidebarClose');
            const backdrop = document.querySelector('.sidebar-backdrop');
            const DESKTOP = 992;

            if (!toggleBtn) return;

            const setAria = () => {
                const isDesktop = window.innerWidth >= DESKTOP;
                const expanded = isDesktop
                ? !app.classList.contains('sidebar-collapsed')
                : app.classList.contains('sidebar-open');
                toggleBtn?.setAttribute('aria-expanded', String(expanded));
            };

            const restore = () => {
                const collapsed = localStorage.getItem('sidebarCollapsed') === '1';
                if (window.innerWidth >= DESKTOP) {
                app.classList.toggle('sidebar-collapsed', collapsed);
                } else {
                app.classList.remove('sidebar-collapsed'); // ensure normal mobile state
                }
                setAria();
            };

            const toggle = () => {
                const isDesktop = window.innerWidth >= DESKTOP;
                if (isDesktop) {
                app.classList.toggle('sidebar-collapsed');
                localStorage.setItem(
                    'sidebarCollapsed',
                    app.classList.contains('sidebar-collapsed') ? '1' : '0'
                );
                } else {
                app.classList.toggle('sidebar-open'); // open/close drawer
                }
                setAria();
            };

            const closeMobile = () => {
                if (window.innerWidth < DESKTOP) {
                app.classList.remove('sidebar-open');
                setAria();
                }
            };

            toggleBtn?.addEventListener('click', toggle);
            closeBtn?.addEventListener('click', closeMobile);
            backdrop?.addEventListener('click', closeMobile);
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMobile(); });
            window.addEventListener('resize', restore);

            // initialize
            restore();
        })();

        // Search Toggle
        (() => {
            $('#searchBtn').on('click', function(){
                $('#searchField').toggleClass('d-none');
                $('#searchField').find('.search').focus();
            });
        })();

        // Fullscreen Toggle
        (() => {
            $("#fullscreenToggle").on("click", function () {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                    $("#fullscreenToggle").find('i').removeClass('bi-fullscreen-exit').addClass('bi-fullscreen');
                } else {
                    document.documentElement.requestFullscreen();
                    $("#fullscreenToggle").find('i').removeClass('bi-fullscreen').addClass('bi-fullscreen-exit');
                }
            });
        })();

        // Breadcrumbs
        (() => {

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
        })();

        // Panel
        if(document.querySelector('[data-bs-template="panel"]')){
            $('footer.copyright').click(function(){
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
                        callback: {
                            load: function(component, modal){
                                return new Promise((resolve, reject) => {
                                    try {
                                        $.ajax({
                                            url: '/api/core/info',
                                            type: 'GET',dataType: 'json',
                                            success: function(response) {

                                                // Update the component with the response
                                                component.body.container.title.name.text(response.name);
                                                component.body.container.title.version.text(response.version);
                                                component.body.container.copyright.owner.html(response.owner + ' ' + builder.Locale.get('All rights reserved') + '.');
                                                for(const [key, author] of Object.entries(response.authors)){
                                                    $(document.createElement('a')).attr({
                                                        "class": "btn btn-link p-0",
                                                        "href": author.url,
                                                        "target": "_blank",
                                                    }).text(author.name).appendTo(component.body.container.developed)
                                                }
                                                component.body.container.license.button.text(response.license.type);

                                                // Add click event to the version button
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
                                                            component.addClass('modal-primary');
                                                            component.dialog.css({'max-width': '720px'});
                                                            component.body.addClass('text-bg-dark');
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

                                                // Add click event to the license button
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
                                                            component.addClass('modal-primary');
                                                            component.dialog.css({'max-width': '720px'});
                                                            component.body.addClass('text-bg-dark');
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

                                                // Resolve the promise
                                                resolve();
                                            }
                                        });
                                    } catch(e) { reject(e); }
                                });
                            },
                        }
                    },
                    function(modal,component){

                        // Styling
                        component.addClass('modal-primary');
                        component.footer.remove();

                        // Content Container
                        component.body.container = $(document.createElement('div')).attr({
                            "class": "d-flex flex-column justify-content-center align-items-center user-select-none",
                        }).appendTo(component.body);

                        // Logo
                        component.body.container.logo = $(document.createElement('img')).attr({
                            "src": LOGO,
                            "class": "img-fluid",
                            "style": "max-width: 200px; margin-bottom: 20px;",
                        }).appendTo(component.body.container);

                        // Application Name & Version
                        component.body.container.title = $(document.createElement('div')).attr({
                            "class": "d-flex justify-content-center align-items-center",
                        }).appendTo(component.body.container);
                        component.body.container.title.name = $(document.createElement('h2')).appendTo(component.body.container.title);
                        component.body.container.title.version = $(document.createElement('span')).attr({
                            "class": "badge bg-primary ms-2 cursor-pointer",
                        }).appendTo(component.body.container.title);

                        // Copyright
                        component.body.container.copyright = $(document.createElement('div')).attr({
                            "class": "d-flex flex-column justify-content-center align-items-center mt-3",
                        }).appendTo(component.body.container);
                        component.body.container.copyright.header = $(document.createElement('strong')).text(builder.Locale.get('Copyright')).appendTo(component.body.container.copyright);
                        component.body.container.copyright.owner = $(document.createElement('span')).appendTo(component.body.container.copyright)

                        // Developers
                        component.body.container.developed = $(document.createElement('div')).attr({
                            "class": "d-flex flex-column justify-content-center align-items-center mt-3",
                        }).appendTo(component.body.container);
                        component.body.container.developed.header = $(document.createElement('strong')).text(builder.Locale.get('Developed by')).appendTo(component.body.container.developed);

                        // License
                        component.body.container.license = $(document.createElement('div')).attr({
                            "class": "d-flex flex-column justify-content-center align-items-center mt-3",
                        }).appendTo(component.body.container);
                        component.body.container.license.header = $(document.createElement('strong')).text(builder.Locale.get('License')).appendTo(component.body.container.license);
                        component.body.container.license.button = $(document.createElement('btn')).attr({
                            "class": "btn btn-link p-0",
                        }).appendTo(component.body.container.license)

                        // Show Modal
                        modal.show();
                    },
                );
            });
        }
        if(document.querySelector('[data-bs-template="panel"],[data-bs-template="fullscreen"]')){
        }

        // Diagnostic
        if(false){
            (() => {
                // ScrollDiag: find & log scrollable containers, with optional highlighting and listeners.
                const tracked = new Map(); // element/window => handler
                const OV = ['auto', 'scroll', 'overlay'];

                const domPath = (el) => {
                    if (!el || el === document.scrollingElement) return 'document.scrollingElement';
                    const parts = [];
                    while (el && el.nodeType === 1 && el !== document.body) {
                        let seg = el.nodeName.toLowerCase();
                        if (el.id) {
                            seg += `#${el.id}`;
                            parts.unshift(seg);
                            break;
                        }
                        const cls = (el.className || '').toString().trim().split(/\s+/).filter(Boolean).slice(0, 2);
                        if (cls.length) seg += '.' + cls.join('.');
                        const nth = el.parentElement ? Array.prototype.indexOf.call(el.parentElement.children, el) + 1 : 1;
                        seg += `:nth-child(${nth})`;
                        parts.unshift(seg);
                        el = el.parentElement;
                    }
                    return parts.join(' > ') || 'document.scrollingElement';
                };

                const describe = (el) => {
                    const cs = getComputedStyle(el);
                    const rect = el.getBoundingClientRect();
                    return {
                        selector: domPath(el),
                        overflowX: cs.overflowX,
                        overflowY: cs.overflowY,
                        metrics: {
                            clientWidth: el.clientWidth,
                            scrollWidth: el.scrollWidth,
                            clientHeight: el.clientHeight,
                            scrollHeight: el.scrollHeight,
                            scrollLeft: el.scrollLeft,
                            scrollTop: el.scrollTop,
                            rect
                        }
                    };
                };

                const throttle = (fn, wait = 200) => {
                    let t = 0, timer;
                    return function throttled(...args) {
                        const now = Date.now();
                        const remaining = wait - (now - t);
                        if (remaining <= 0) {
                            t = now;
                            fn.apply(this, args);
                        } else {
                            clearTimeout(timer);
                            timer = setTimeout(() => {
                            t = Date.now();
                            fn.apply(this, args);
                            }, remaining);
                        }
                    };
                };

                const isScrollable = (el) => {
                    const cs = getComputedStyle(el);
                    const canY = OV.includes(cs.overflowY) && el.scrollHeight > el.clientHeight;
                    const canX = OV.includes(cs.overflowX) && el.scrollWidth > el.clientWidth;
                    const isRoot = el === document.scrollingElement && el.scrollHeight > el.clientHeight;
                    return { canX, canY, isRoot, any: canX || canY || isRoot };
                };

                const scan = ({ highlight = false, listen = false } = {}) => {
                    // Gather all elements plus the root scrolling element
                    const all = new Set([...document.querySelectorAll('*'), document.scrollingElement]);

                    let count = 0;
                    all.forEach((el) => {
                        if (!(el instanceof Element)) return;
                        const s = isScrollable(el);
                        if (!s.any) return;

                        count++;
                        const info = describe(el);
                        console.groupCollapsed(`Scrollable: ${info.selector}`);
                        console.table(info.metrics);
                        console.log(info, el);
                        console.groupEnd();

                        if (highlight) {
                            // Non-destructive: outline only (you can remove it via stop()).
                            el.style.outline = '2px dashed var(--bs-primary, #0d6efd)';
                            el.style.outlineOffset = '2px';
                        }

                        if (listen) {
                            const logScroll = throttle(() => {
                                const i = describe(el);
                                console.log('Scrolled →', i.selector, i.metrics, el);
                            }, 200);

                            if (s.isRoot) {
                                // Root scrolling happens on window
                                window.addEventListener('scroll', logScroll, { passive: true });
                                tracked.set(window, logScroll);
                            } else {
                                el.addEventListener('scroll', logScroll, { passive: true });
                                tracked.set(el, logScroll);
                            }
                        }
                    });

                    console.info(`ScrollDiag: found ${count} scrollable container(s).`);
                    return count;
                };

                const stop = () => {
                    tracked.forEach((handler, target) => {
                        if (target === window) {
                            window.removeEventListener('scroll', handler);
                        } else {
                            try {
                                target.removeEventListener('scroll', handler);
                                // Clear highlight if we set one
                                target.style.outline = '';
                                target.style.outlineOffset = '';
                            } catch (_) {}
                        }
                    });
                    tracked.clear();
                    console.info('ScrollDiag: listeners removed & highlights cleared.');
                };

                window.ScrollDiag = { scan, stop };
            })();
        }
    })();
});

// // Configure Toast
// $(document).ready(function(){
//     builder.Toast.prependTo('body');
//     builder.Toast.position('bottom-end');
// });

// // Set Locale's callback
// builder.Locale._callback = function(key, locale){
//     $.ajax({
//         url: '/api/locale/get?locale='+locale+'&key=' + key,
//         type: 'GET',dataType: 'json'
//     });
// }

// // Retrieve the locales
// $.ajax({
//     url: '/api/locale/translations',
//     type: 'GET',dataType: 'json',
//     success: function(response){
//         builder.Locale.save('en-ca', response);
//     },
// });
// $.ajax({
//     url: '/api/locale/current',
//     type: 'GET',dataType: 'json',
//     success: function(response){
//         var locale = response;
//         $.ajax({
//             url: '/api/locale/translations?locale=' + locale,
//             type: 'GET',dataType: 'json',
//             success: function(response){
//                 builder.Locale.save(locale, response);
//             },
//         });
//     },
// });

// // Retrieve the libraries
// $.ajax({
//     url: '/api/locale/locales',
//     type: 'GET',dataType: 'json',
//     success: function(response){
//         var locales = [];
//         for(const [id, text] of Object.entries(response)){
//             locales.push({id: id, text: text});
//         }
//         builder.Option.save('locales', locales);
//     },
// });
// $.ajax({
//     url: '/api/library/fetch',
//     type: 'GET',dataType: 'json',
//     success: function(response){
//         for(const [library, records] of Object.entries(response.options)){
//             switch(library){
//                 case 'states':
//                     for(const [target, record] of Object.entries(records)){
//                         builder.Option.save(library, record, target);
//                     }
//                     break;
//                 default:
//                     builder.Option.save(library, records);
//                     break;
//             }
//         }
//     },
// });

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

