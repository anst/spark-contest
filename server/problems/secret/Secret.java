import static java.lang.System.*;import static java.lang.Math.*;import static java.lang.Character.*;import java.io.*;import java.text.*;import java.util.*;import java.util.regex.*;import java.math.*;
public class Secret {
	public static void main(String[] args) throws IOException {
		Scanner sc = new Scanner(new File("Secret.in"));
		int __ = Integer.parseInt(sc.nextLine());
		for(int _ = 0; _ < __; _++) {
			String[] word = sc.nextLine().split("-");
			String s = "";
			for(String aha:word) {
				String[] noe = aha.split("\\+");
				for(String ahah:noe) {
					char[] lol = ahah.toCharArray();
					int pow = 0;
					int val = 0;
					for(int x = 0;x < lol.length; x++) {
						if(x!=0) {
							if(lol[x-1]==lol[x]&&lol[x]=='>') {
								pow++;
								val+=Math.pow(2,pow);
							}
							else if(lol[x-1]==lol[x]&&lol[x]=='<'){
								pow++;
								val-=Math.pow(2,pow);
							}
							else {
								if(lol[x]=='>') {
									pow=0;
									val+=Math.pow(2,pow);
								} else {
									pow=0;
									val-=Math.pow(2,pow);
								}
							}
						}
						else{
							if(lol[x]=='>') {
								val+=Math.pow(2,pow);
							} else {
								val-=Math.pow(2,pow);
							}
						}
					}
					s+=((char)val);
				}
				s+=" ";
			}
	
			System.out.println(s.trim());	
		}
		
	}
}
