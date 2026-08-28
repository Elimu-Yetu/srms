<?php
logout();
flash('info', 'You have been signed out.');
redirect('login');
