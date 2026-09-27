import { Provider } from "react-redux"
import { QueryClientProvider } from "@tanstack/react-query"
import { store } from "@/store"
import { queryClient } from "@/lib/queryClient"
import { ReactQueryDevtools } from "@tanstack/react-query-devtools";

export function AppProviders({ children }) {
  return (
    <Provider store={store}>
      <QueryClientProvider client={queryClient}>
        {children}
        <ReactQueryDevtools/>
      </QueryClientProvider>
    </Provider>
  )
}
