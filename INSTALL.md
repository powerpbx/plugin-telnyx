# Installation

You will need an installed and working playSMS, and assumed your playSMS is installed with these setups:

- Your playSMS web files is in `/var/www/html/playsms`
- Your playSMS database is `playsms`
- Your playSMS database username is `root`
- You access the database, you know the password

Follow below steps in order:

1. Clone this repo to your playSMS server

   ```
   cd ~
   git clone https://github.com/powerpbx/plugin-telnyx.git
   cd plugin-telnyx
   ```

2. Copy gateway to playSMS `plugin/gateway/`

   ```
   cp -rR web/plugin/gateway/telnyx /var/www/html/playsms/plugin/gateway/
   ```

3. Insert `web/plugin/gateway/telnyx/db/install.sql` to playSMS database

   ```
   mysql -uroot -p playsms < web/plugin/gateway/telnyx/db/install.sql
   ```

4. Restart `playsmsd`

   ```
   playsmsd restart
   playsmsd check
   ```
