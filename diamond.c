#include <stdio.h>
 
int main()
{
  int n, c, k, space = 1;
 
  printf("Enter number of rows\n");
  scanf("%d", &n);
 
  space = n - 1;
 
  for (k = 1; k <= n; k++)
  {
    for (c = 1; c <= space; c++)
      printf(" ");
 
    space--;
 
    for (c = 1; c <= 2*k-1; c++)
      printf("*");
 
    printf("\n");
  }
 
  space = 1;
 
  for (k = 1; k <= n - 1; k++)
  {
    for (c = 1; c <= space; c++)
      printf(" ");
 
    space++;
 
    for (c = 1 ; c <= 2*(n-k)-1; c++)
      printf("*");
 
    printf("\n");
  }
 
  return 0;
}#include<stdio.h>
 
main()
{
    int n, c, k, space, count = 1;
 
    printf("Enter number of rows\n");
    scanf("%d",&n);
 
    space = n;
 
    for ( c = 1 ; c <= n ; c++)
    {
        for( k = 1 ; k < space ; k++)
           printf(" ");
 
        for ( k = 1 ; k <= c ; k++)
        {
            printf("*");
 
            if ( c > 1 && count < c)
            {
                 printf("A");    
                 count++; 
            }      
        }    
 
        printf("\n");
        space--;
        count = 1;
    }
    return 0;
}#include<stdio.h>

int main()
{
	int row,n,temp,c;
	printf("Enter rows");
	scanf("%d",&n);

	temp=n;
	for(row=1;row<=n;row++)
	{
		for(c=1;c<row;c++)
			printf("");
		temp--;
		
		for(c=1;c=2*row-1;c++)
			printf("*");

		printf("/n");
	}
	return 0;
}
