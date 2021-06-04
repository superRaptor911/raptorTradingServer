let SessionLoad = 1
let s:so_save = &so | let s:siso_save = &siso | set so=0 siso=0
let v:this_session=expand("<sfile>:p")
silent only
cd ~/program/servers/cucekTradings/server
if expand('%') == '' && !&modified && line('$') <= 1 && getline(1) == ''
  let s:wipebuf = bufnr('%')
endif
set shortmess=aoO
badd +30 sql.sql
badd +48 coin.php
badd +402 transction.php
badd +1 userCoins.php
badd +79 users.php
badd +49 ~/.config/nvim/UltiSnips/php.snippets
badd +123 recomputeCoins.php
badd +28 donations.php
badd +1 database.php
badd +36 queryDatabase.php
badd +33 messages.php
badd +19 utility.php
badd +1 secret.php
badd +28 setup.sh
badd +94 tests/test1.php
badd +33 source/userManagement.php
badd +37 source/transactionManagement.php
badd +17 source/coinManagement.php
argglobal
%argdel
edit tests/test1.php
set splitbelow splitright
set nosplitbelow
set nosplitright
wincmd t
set winminheight=0
set winheight=1
set winminwidth=0
set winwidth=1
argglobal
let s:l = 94 - ((30 * winheight(0) + 22) / 45)
if s:l < 1 | let s:l = 1 | endif
exe s:l
normal! zt
94
normal! 039|
tabnext 1
if exists('s:wipebuf') && getbufvar(s:wipebuf, '&buftype') isnot# 'terminal'
  silent exe 'bwipe ' . s:wipebuf
endif
unlet! s:wipebuf
set winheight=1 winwidth=20 winminheight=1 winminwidth=1 shortmess=filnxtToOFc
let s:sx = expand("<sfile>:p:r")."x.vim"
if file_readable(s:sx)
  exe "source " . fnameescape(s:sx)
endif
let &so = s:so_save | let &siso = s:siso_save
doautoall SessionLoadPost
unlet SessionLoad
" vim: set ft=vim :
