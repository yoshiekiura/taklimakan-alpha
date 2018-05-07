from datetime import datetime


def timer(fn):
    def new_function(*args, **kwargs):
        before = datetime.now()
        x = fn(*args, **kwargs)
        after = datetime.now()
        print("Method {1} finished.  Elapsed Time = {0}".format(after - before, fn.__name__))
        return x
    return new_function