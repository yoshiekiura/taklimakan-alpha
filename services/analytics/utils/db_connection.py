import os
from datetime import datetime

import MySQLdb
import mysql.connector
import sshtunnel

from utils.timer import timer


def log_arg(fn):
    def new_function(*args, **kwargs):
        print(args[1])
        x = fn(*args, **kwargs)
        return x

    return new_function


# TODO обработка переменных окружения - добавить еще один конструктор, либо вынести во вне
class DbConnection:
    ssh_server = None
    db = None

    @staticmethod
    def get_instance(config):
        need_ssh = True
        if ('DB_HOST' in os.environ.keys()) and ('DB_USER' in os.environ.keys()) and (
                    'DB_PASSWORD' in os.environ.keys()):
            config['local']['host'] = os.environ['DB_HOST']
            config['db']['user'] = os.environ['DB_USER']
            config['db']['pass'] = os.environ['DB_PASSWORD']
            need_ssh = False
        return DbConnection(need_ssh, config)

    def __init__(self, need_ssh, config):
        if need_ssh:
            sshtunnel.SSH_TIMEOUT = float(config['ssh']['ssh_timeout'])
            sshtunnel.TUNNEL_TIMEOUT = float(config['ssh']['tunnel_timeout'])
            self.ssh_server = sshtunnel.SSHTunnelForwarder((config['ssh']['host'], int(config['ssh']['port'])),
                                                           ssh_username=config['ssh']['username'],
                                                           ssh_password=config['ssh']['password'],
                                                           remote_bind_address=(config['ssh']['db_remote_bind_address'],
                                                                                int(config['ssh'][
                                                                                        'db_remote_mysql_port'])),
                                                           local_bind_address=(config['ssh']['db_local_bind_address'],
                                                                               int(config['ssh'][
                                                                                       'db_local_mysql_port'])))
            self.ssh_server.start()

            self.db = mysql.connector.connect(
                user=config['db']['user'],
                password=config['db']['pass'],
                host=config['ssh']['db_local_bind_address'],
                database=config['db']['name'],
                port=config['ssh']['db_local_mysql_port'])

        else:
            self.db = MySQLdb.connect(host=config['local']['host'], user=config['db']['user'],
                                      passwd=config['db']['pass'], db=config['db']['name'])
        print("Connected to DB " + ("(local)" if need_ssh else "(server)"))
        self.analytics_table = config['db']['analytics_table']
        self.price_table = config['db']['price_table']
        self.index_table = config['db']['index_table']
        self._check_analytics_table()

    def _check_analytics_table(self):
        cursor = self.db.cursor()
        # TODO move creation query to config ?
        query = "CREATE TABLE IF NOT EXISTS %s (dt DATETIME, pair VARCHAR(20), type_id INT(2), value FLOAT, is_extrapolated tinyint(1) default '0', PRIMARY KEY (dt, pair, type_id));" % self.analytics_table
        cursor.execute(query)
        cursor.close()

    #@log_arg
    #@timer
    def get_list(self, query):
        cursor = self.db.cursor()
        cursor.execute(query)
        results = cursor.fetchall()
        cursor.close()
        return results

    #@log_arg
    #@timer
    def get_single_value(self, query):
        cursor = self.db.cursor()
        cursor.execute(query)
        res = cursor.fetchone()
        cursor.close()
        if res is None:
            return None
        return res[0]

    #@log_arg
    #@timer
    def execute(self, query):
        cursor = self.db.cursor()
        cursor.execute(query)
        self.db.commit()
        cursor.close()

    def __enter__(self):
        return self

    def __exit__(self, exc_type, exc_value, traceback):
        if self.db is not None:
            self.db.disconnect()
        if self.ssh_server is not None:
            self.ssh_server.stop()
