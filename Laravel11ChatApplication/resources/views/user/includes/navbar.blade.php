

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Include necessary CSS and JS libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <style>
        .dropdown-menu {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
            <a class="navbar-brand brand-logo mr-5" href="index.html"><img src="/images/logo.svg" class="mr-2" alt="logo" /></a>
            <a class="navbar-brand brand-logo-mini" href="index.html"><img src="/images/logo-mini.svg" alt="logo" /></a>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                <span class="icon-menu"></span>
            </button>
            <ul class="navbar-nav mr-lg-2">
                <li class="nav-item nav-search d-none d-lg-block">
                    <div class="input-group">
                        <div class="input-group-prepend hover-cursor" id="navbar-search-icon">
                            <span class="input-group-text" id="search"></span>
                        </div>
                    </div>
                </li>
            </ul>
            <ul class="navbar-nav navbar-nav-right">
                <li class="nav-item dropdown">
                    <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-toggle="dropdown">
                    <i class="fas fa-bell mx-0"></i>

                    </a>                  
                    
                 <b>   <span id="notificationCount" class="count">&nbsp;  </span> <!-- Initial value here -->
                 </b>
                    <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
                        <p class="mb-0 font-weight-normal float-left dropdown-header">Notifications</p>
                       
                        <a class="dropdown-item preview-item">
                            <div class="preview-thumbnail">
                                <div class="preview-icon bg-success">
                                    <i class="ti-info-alt mx-0"></i>
                                </div>
                            </div>
                            <div class="preview-item-content">
                                <h6 class="preview-subject font-weight-normal">New Message</h6>
                                <p class="font-weight-light small-text mb-0 text-muted">
                                   
                                </p>
                            </div>
                        </a>
                      
                    </div>
                </li>
                <li class="nav-item nav-profile dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
                        @if($LoggedUserInfo->picture)
                        <img src="{{ asset('storage/' . $LoggedUserInfo->picture) }}" alt="profile" class="rounded-circle" style="width: 35px; height: 35px;">
                        @else
                        <img src="/images/faces/face28.jpg" alt="profile" class="rounded-circle" style="width: 35px; height: 35px;">
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                        <a class="dropdown-item" href="{{ route('user.profileview') }}">
                            <i class="ti-user text-primary"></i>
                            Profile
                        </a>
                        <form id="logout-form" action="{{ route('user.logout') }}" method="GET" style="display: inline;">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="ti-power-off text-primary"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
            <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
                <span class="icon-menu"></span>
            </button>
        </div>
    </nav>

    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var notificationCount = document.getElementById('notificationCount');

        var pusher = new Pusher('b23d71886d55f985f153', {
            cluster: 'ap2'
        });

        var loggedUserId = '{{ $LoggedUserInfo['id'] }}';
        var channel = pusher.subscribe('user.' + loggedUserId);

        channel.bind('new-message', function(data) {
            if (data.userId == loggedUserId) {
                toastr.options = {
                    "closeButton": true,          // Show close button
                    "progressBar": true,          // Show progress bar
                    "positionClass": "toast-top-right",  // Position of the toast
                    "timeOut": "300000",          // Set timeout to 5 minutes (300,000 milliseconds)
                    "extendedTimeOut": "300000",  // Extend timeout if the user hovers over the toast
                    "tapToDismiss": false         // Prevent dismissing by clicking on the toast itself
                };
                toastr.info('Admin sent you a message: ' + data.message, 'New Message');

                // Update notification count
                var currentCount = parseInt(notificationCount.textContent) || 0;
                notificationCount.textContent = currentCount + 1;

                // Prepend new message to the dropdown menu
                var dropdownMenu = document.querySelector('.dropdown-menu');
                var messageItem = document.createElement('a');
                messageItem.classList.add('dropdown-item', 'preview-item');
                messageItem.innerHTML = `
                    <div class="preview-thumbnail">
                        <div class="preview-icon bg-success">
                            <i class="ti-info-alt mx-0"></i>
                        </div>
                    </div>
                    <div class="preview-item-content">
                        <h6 class="preview-subject font-weight-normal">New Message</h6>
                        <p class="font-weight-light small-text mb-0 text-muted">
                            ${data.message}<br>
                            Just now
                        </p>
                    </div>
                `;
                dropdownMenu.prepend(messageItem); // Prepend to show it first
            }
        });
    });
</script>


</body>
</html>
