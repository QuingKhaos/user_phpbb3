<form id="phpbb3" action="#" method="post">
    <div class="section">
        <fieldset class="personalblock">
            <legend><strong>phpBB3</strong></legend>
            <p>
                <label for="phpbb3_db_host"><?php echo $l->t('DB Host');?></label>
                <input type="text" id="phpbb3_db_host" name="phpbb3_db_host"
                    value="<?php echo $_['phpbb3_db_host']; ?>" />
            </p>
            <p>
                <label for="phpbb3_db_name"><?php echo $l->t('DB Name');?></label>
                <input type="text" id="phpbb3_db_name" name="phpbb3_db_name"
                    value="<?php echo $_['phpbb3_db_name']; ?>" />
            </p>
            <p>
                <label for="phpbb3_db_prefix"><?php echo $l->t('DB Prefix');?></label>
                <input type="text" id="phpbb3_db_prefix" name="phpbb3_db_prefix"
                    value="<?php echo $_['phpbb3_db_prefix']; ?>" />
            </p>
            <p>
                <label for="phpbb3_db_user"><?php echo $l->t('DB User');?></label>
                <input type="text" id="phpbb3_db_user" name="phpbb3_db_user"
                    value="<?php echo $_['phpbb3_db_user']; ?>" />
            </p>
            <p>
                <label for="phpbb3_db_pass"><?php echo $l->t('DB Password');?></label>
                <input type="password" id="phpbb3_db_pass" name="phpbb3_db_pass"
                    value="<?php echo $_['phpbb3_db_pass']; ?>" />
            </p>
            <p>
                <label for="phpbb3_assign_group"><?php echo $l->t('Assign to Group');?></label>
                <input type="text" id="phpbb3_assign_group" name="phpbb3_assign_group"
                       title="Group to assign PHPBB3 users to - leave blank for none."
                       value="<?php echo $_['phpbb3_assign_group']; ?>" />
            </p>
            <input type="submit" value="Save" />
        </fieldset>
    </div>
</form>
