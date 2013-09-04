import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.PrintStream;
import java.lang.reflect.InvocationTargetException;
import java.lang.reflect.Method;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLClassLoader;
import java.security.Permission;

public class ContestJudge {

	public static void main(String[] args)
			throws Exception {

		// @param
		String dir = args[0];
		String classname = args[1];

		// fetch the class of the submission
		File f = new File(dir);

		URLClassLoader urlcl = null;
		try {
			URL[] cp=new URL[]{f.toURI().toURL()};
			urlcl = new URLClassLoader(cp);
		} catch (MalformedURLException e){
			e.printStackTrace();
			System.err.println("Misconfigured directory path");
		}
		final Class<?> testclass = urlcl.loadClass(classname);

		urlcl.close();

		// redirect stdout
		final boolean debug = false;
		PrintStream ps = null;
		final ByteArrayOutputStream bao = new ByteArrayOutputStream();
		if(debug)
			ps = new PrintStream(bao);
		else{
			ps = new PrintStream(new FileOutputStream(testclass.getName()+".out"));
			System.setOut(ps);
			System.setErr(ps);
		}

		final SecurityManager sm = new SecurityManager(){
			@Override
			public void checkPermission(Permission perm) {
				check(perm);
			} 

			@Override
			public void checkPermission(Permission perm, Object context) {
				check(perm);
			}

			private void check(Permission perm) {
				throw new SecurityException("Permission denied: "+perm.getActions());
			}
		};
		
		Thread thread = new Thread(){
			@Override
			public void run() {
				// enable sandbox
				System.setSecurityManager(sm);
				
				// invoke main method
				Class<?>[] argTypes = new Class<?>[] { String[].class };
				try{
					Method main = testclass.getDeclaredMethod("main", argTypes);
					main.invoke(null, (Object)new String[0]);
				}
				catch (InvocationTargetException e) {
					e.printStackTrace();
				} catch (NoSuchMethodException e) {
					e.printStackTrace();
					System.err.println("No main method");
				} catch (SecurityException e) {
					e.printStackTrace();
					System.err.println("Stop trying to hack us");
				} catch (IllegalAccessException e) {
					e.printStackTrace();
				} catch (IllegalArgumentException e) {
					e.printStackTrace();
				}
				
				if(debug){
					System.out.println(bao.toString());
				}
			}
		};
		thread.start();


	}

}
