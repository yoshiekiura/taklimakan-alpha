import time
from datetime import datetime, timezone, timedelta



datetime_object = datetime.strptime("2018-04-05", '%Y-%m-%d')
print(time.mktime(datetime_object.timetuple()))
