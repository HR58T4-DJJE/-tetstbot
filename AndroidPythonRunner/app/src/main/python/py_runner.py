import io
import os
import runpy
import sys
import traceback


def run_script(main_path: str) -> str:
    """
    Run the provided main.py script in-process while capturing stdout/stderr.
    Returns combined output. Sets CWD to the script directory.
    """
    buffer = io.StringIO()
    old_stdout, old_stderr, old_cwd, old_argv = sys.stdout, sys.stderr, os.getcwd(), sys.argv
    try:
        sys.stdout = buffer
        sys.stderr = buffer
        script_dir = os.path.dirname(main_path)
        if script_dir:
            os.chdir(script_dir)
        sys.argv = [main_path]
        runpy.run_path(main_path, run_name="__main__")
    except SystemExit as exc:
        buffer.write(f"\n[exit] {exc}\n")
    except Exception:
        traceback.print_exc(file=buffer)
    finally:
        sys.stdout, sys.stderr = old_stdout, old_stderr
        os.chdir(old_cwd)
        sys.argv = old_argv
    return buffer.getvalue()
