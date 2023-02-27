#!/usr/local/munkireport/munkireport-python3
# Written for MunkiReport by tuxudo
# Parts of this script are from https://github.com/carlashley/tccprofile

import os
import subprocess
import sys
import plistlib
import glob
import sqlite3
import platform

def get_dbs():
           
    # Get system TCC db
    if os.path.isfile('/Library/Application Support/com.apple.TCC/TCC.db'):
        user_paths = "/Library/Application Support/com.apple.TCC/TCC.db" + "\n"
    else:
        user_paths = ""
    
    # Get all users' home folders
    cmd = ['/usr/bin/dscl', '.', '-readall', '/Users', 'NFSHomeDirectory']
    proc = subprocess.Popen(cmd, shell=False, bufsize=-1,
                            stdin=subprocess.PIPE,
                            stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    (output, unused_error) = proc.communicate()
    
    for user in output.decode().split('\n'):
        if 'NFSHomeDirectory' in user and '/var/empty' not in user:
            userpath = user.replace("NFSHomeDirectory: ", "")+'/Library/Application Support/com.apple.TCC/TCC.db'
            if os.path.isfile(userpath):
                user_paths = userpath + "\n" + user_paths  

    return user_paths[:-1]

def process_dbs(dbs):

    output = []

    for db in dbs.split('\n'):
        tcc = ReadTCC(tcc_db_path=db)

        for entry in tcc.read_db():
            result = {}

            result['dbpath'] = db.replace("Library/Application Support/com.apple.TCC/TCC.db", "")
            
            # Skip iCloud and HomeKit entries
            if entry[0] == "kTCCServiceUbiquity" or entry[0] == "kTCCServiceWillow":
                continue
            
            result['service'] = entry[0]
            result['client'] = entry[1]
            result['client_type'] = entry[2]
            if entry[3] >= 2:
                result['allowed'] = 1
            else:
                result['allowed'] = entry[3]
            result['prompt_count'] = entry[4]

            # Process other columns for newer OS versions Mojave (Darwin 18)
            if getDarwinVersion() >= 18:
                if entry[5] == "UNUSED":
                    result['indirect_object_identifier'] = ""
                else:
                    result['indirect_object_identifier'] = entry[5]
                result['last_modified'] = str(entry[6])

            output.append(result)

    return output

def getDarwinVersion():
    """Returns the Darwin version."""
    # Catalina -> 10.15.7 -> 19.6.0 -> 19
    darwin_version_tuple = platform.release().split('.')
    return int(darwin_version_tuple[0])

class Sqlite_db():
    '''
    Wrapper for sqlite3 that includes some budget error/exception handling.
    Usage:
        Sqlite_db.connect(db)
            Tries to connect, if connection doesn't already exist
        Sqlite_db.query('SELECT something FROM table', fetch=False)
            Makes the query against the database.
                fetch=True will return selected items.
                Otherwise query is made as supplied
        Sqlite_db.commit_change()
            Commits changes made to the database.
        Sqlite_db.disconnect(db)
            Tries to disconnect.
    '''
    connection = ''
    c = ''

    def connect(self, db):
        try:
            self.connection.execute("")
        except Exception:
            try:
                self.connection = sqlite3.connect(db)
                self.c = self.connection.cursor()
            except:
                print("Error: Unable to access TCC database! Does the MunkiReport have permission?")
                sys.exit(1)

    def disconnect(self, db):
        try:
            self.connection.execute("")
            try:
                self.connection.close()
                try:
                    self.connection.execute("")
                except Exception:
                    raise
            except Exception:
                raise
        except Exception:
            pass

    def query(self, query_string, fetch=False):
        try:
            self.c.execute(query_string)
            if not fetch:
                self.c.execute(query_string)
            else:
                self.c.execute(query_string)
                return self.c.fetchall()
        except Exception:
            raise


class ReadTCC():
    def __init__(self, tcc_db_path):
        self.tcc_db = tcc_db_path.rstrip('/')
        self.tcc_db = os.path.expandvars(self.tcc_db)
        self.tcc_db = os.path.expanduser(self.tcc_db)
        self.sqlite = Sqlite_db()

    def read_db(self):
        if self.tcc_db.startswith('/Library') and os.getuid() != 0:
            print(('<result>You must be root to read {} </result>'.format(self.tcc_db)))
            sys.exit(1)
        else:
            self.sqlite.connect(self.tcc_db)

            # Get list of database columns
            columns = [i[1] for i in self.sqlite.query('PRAGMA table_info(access)', fetch=True)]

            # Process other columns for newer OS versions Big Sur
            if 'service' in columns and 'client' in columns and 'client_type' in columns and 'auth_value' in columns and 'auth_reason' in columns and 'indirect_object_identifier' in columns and 'last_modified' in columns:
                query = self.sqlite.query('SELECT service, client, client_type, auth_value, auth_reason, indirect_object_identifier, last_modified FROM access', fetch=True)
            # Process other columns for Mojave and Catalina
            elif 'service' in columns and 'client' in columns and 'client_type' in columns and 'allowed' in columns and 'prompt_count' in columns and 'indirect_object_identifier' in columns and 'last_modified' in columns:
                query = self.sqlite.query('SELECT service, client, client_type, allowed, prompt_count, indirect_object_identifier, last_modified FROM access', fetch=True)
            else:
                query = self.sqlite.query('SELECT service, client, client_type, allowed, prompt_count FROM access', fetch=True)

            self.sqlite.disconnect(self.tcc_db)
            return query

def main():
    """Main"""

    # Check Darwin version, must be greater than Darwin 13 (Mac 10.9)
    if getDarwinVersion() < 13:
        print ('<result>TCC module requires 10.9 or higher</result>')
        exit(0)

    # Get information about the TCC database
    dbs = get_dbs()
    result = process_dbs(dbs)

    # Write TCC info results to cache
    cachedir = '/usr/local/munkireport/scripts/cache'
    output_plist = os.path.join(cachedir, 'tcc_jamf.plist')
    plistlib.writePlist(result, output_plist)
    try:
        plistlib.writePlist(result, output_plist)
    except:
        with open(output_plist, 'wb') as fp:
            plistlib.dump(result, fp, fmt=plistlib.FMT_XML)

if __name__ == "__main__":
    main()
