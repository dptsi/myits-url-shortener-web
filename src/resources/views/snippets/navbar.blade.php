<div class="container-fluid">
    <nav role="navigation" class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <!-- Output sign in/sign out buttons appropriately -->

            <div class="navbar-header">
                <a class="navbar-brand" href="https://shortener.its.ac.id">

                    <img src='/img/myits-link-shortener-white.png' alt="Brand" width="90" style="top:0px; margin-left:15px; margin-top:-8px" />
                </a>
            </div>



            <ul id="navbar" class="nav navbar-collapse collapse navbar-nav" id="nbc">
                <li><a @if (isset($role)) href="{{ route('admin') }}#admin" @else href="{{ url('links') }}" @endif>Links 
                
                    </a>
                </li>

                @if (empty(session('username')))
                @else
                <li class="visible-xs"><a href="{{ route('admin') }}">Dashboard</a></li>
                <li class="visible-xs"><a href="{{ route('logout') }}">Logout</a></li>
                @endif
            </ul>

            <ul id="navbar" class="nav pull-right navbar-nav hidden-xs">
                <li class="divider-vertical"></li>
                <div class='nav pull-right navbar-nav'>
                    <li class='dropdown'>
                        <a class="dropdown-toggle login-name" href="#" data-toggle="dropdown">{{session('username')}} <strong class="caret"></strong></a>
                        <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu">
                            <li><a tabindex="-1" href="{{ route('admin') }}">Dashboard</a></li>
                            <li><a tabindex="-1" href="{{ route('logout') }}">Logout</a></li>
                        </ul>
                    </li>
                </div>
            </ul>
        </div>
    </nav>
</div>