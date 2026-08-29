#!/bin/bash

#
# -------------------------------------------------------------------------
# consumables plugin for GLPI
# Copyright (C) 2015-2026 by the consumables Development Team.
#
# https://github.com/InfotelGLPI/consumables
# -------------------------------------------------------------------------
#
# LICENSE
#
# This file is part of consumables.
#
# consumables is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# consumables is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with consumables. If not, see <http://www.gnu.org/licenses/>.
# --------------------------------------------------------------------------
#

xgettext *.php */*.php */*/*.php */*/*/*.php --copyright-holder='Consumables Development Team' --package-name='GLPI - Consumables plugin' -o locales/glpi.pot -L PHP --add-comments=TRANS --from-code=UTF-8 --force-po  \
	--keyword=_n:1,2,4t --keyword=__s:1,2t --keyword=__:1,2t --keyword=_e:1,2t --keyword=_x:1c,2,3t \
	--keyword=_ex:1c,2,3t --keyword=_nx:1c,2,3,5t --keyword=_sx:1c,2,3t \
	`# php-cs-fixer adds a trailing comma to every multiline call, and xgettext counts it as` \
	`# one extra argument, so the specs above stop matching and strings are silently dropped.` \
	`# These duplicates accept the same calls with that extra argument. Keep both lists in sync.` \
	--keyword=_n:1,2,5t --keyword=__s:1,3t --keyword=__:1,3t --keyword=_e:1,3t --keyword=_x:1c,2,4t \
	--keyword=_ex:1c,2,4t --keyword=_nx:1c,2,3,6t --keyword=_sx:1c,2,4t



