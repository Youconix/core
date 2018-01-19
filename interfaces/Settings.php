<?php

interface Settings extends ConfigReader
{

    const SSL_DISABLED = 0;

    const SSL_LOGIN = 1;

    const SSL_ALL = 2;

    const REMOTE = 'https://framework.youconix.nl/2/';

    const MAJOR = 2;
}