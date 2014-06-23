MYDIGIPASS.com integration for Joomla! by compojoom
---------
MYDIGIPASS.COM protects your online accounts by adding an additional security layer to stop 
hackers who want to steal your identity and credentials.

With the help of the package in this repository you'll be able to integrate the mydigipass service
with your joomla site in just a few minutes.

## Downloading an intallable zip package

If you are looking for a Joomla! installable zip package of this repository navigate to
<https://compojoom.com/downloads/official-releases-stable/mydigipass>

## Building the package from github

### Prerequisites

In order to build the installation packages of this library you need to have
the following tools:

- A command line environment. Bash under Linux / Mac OS X . On Windows
  you will need to run most tools using an elevated privileges (administrator)
  command prompt.
- The PHP CLI binary in your path

- Command line Subversion and Git binaries(*)

- PEAR and Phing installed, with the Net_FTP and VersionControl_SVN PEAR
  packages installed

You will also need the following path structure on your system

- pkg_mydigipass - This repository
- buildtools - Compojoom build tools (https://github.com/compojoom/buildtools)

### Creating the zip package

Navigate to the pkg_mydigipass/builds directory. Rename the build.properties.txt to build properties
and execute the following command:

	phing

## COPYRIGHT AND DISCLAIMER
Compojoom library -  Copyright (c) 2008-2014 Compojoom.com

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the
Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see http://www.gnu.org/licenses/.
